<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use App\Models\AppSetting;
use App\Models\Company;
use App\Models\ContractDocument;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\FirstParty;
use App\Models\User;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ContractDocumentController extends Controller
{
    private const TEMPLATE_FIELDS = [
        'location',
        'project_name',
        'first_party_name',
        'first_party_position',
        'first_party_company',
        'first_party_address',
        'second_party_name',
        'second_party_address',
        'second_party_ktp',
        'duration_text',
        'payment_method',
        'contract_value_text',
        'contract_value',
        'payment_terms',
        'scope_of_work',
        'rights_obligations',
        'hki_terms',
        'nda_terms',
        'sanctions_terms',
        'dispute_terms',
        'bank_name',
        'bank_account',
        'bank_account_name',
        'notes',
    ];

    public function index(Request $request)
    {
        $contracts = ContractDocument::with(['employee.company', 'creator', 'signer'])
            ->when($request->employee_id, fn ($q) => $q->where('employee_id', $request->employee_id))
            ->when($request->company_id, fn ($q) => $q->whereHas('employee', fn ($e) => $e->where('company_id', $request->company_id)))
            ->when($request->search, fn ($q) => $q->where('contract_number', 'like', '%' . $request->search . '%'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $employees = Employee::with('company')->orderBy('name')->get();
        $companies = Company::orderBy('name')->pluck('name', 'id');

        return view('contract-documents.index', compact('contracts', 'employees', 'companies'));
    }

    public function create(Request $request)
    {
        $employees = Employee::with('company')->orderBy('name')->get();
        $firstParties = FirstParty::where('is_active', true)->orderBy('name')->get();
        $selectedEmployeeId = $request->integer('employee_id');
        $suggestedContractNumber = $this->generateContractNumber();
        $contractTemplate = $this->getContractTemplate();

        return view('contract-documents.create', compact('employees', 'firstParties', 'selectedEmployeeId', 'suggestedContractNumber', 'contractTemplate'));
    }

    public function store(Request $request)
    {
        $data = $this->validateContract($request);
        $data = $this->syncDurationFields($data);

        if (empty($data['contract_number'])) {
            $data['contract_number'] = $this->generateContractNumber();

            // Avoid accidental duplicates when data is edited manually.
            while (ContractDocument::where('contract_number', $data['contract_number'])->exists()) {
                $data['contract_number'] = $this->generateContractNumber();
            }
        }

        if ($request->hasFile('contract_file')) {
            $file = $request->file('contract_file');
            $data['file_path'] = $file->store('contract-documents/' . $data['employee_id'], 'private');
            $data['original_name'] = $file->getClientOriginalName();
            $data['mime_type'] = $file->getMimeType();
            $data['file_size'] = $file->getSize();
        }

        $data['created_by'] = Auth::id();

        // Auto-fill penandatangan from FirstParty master if not supplied
        if (empty($data['penandatangan_p1_name'])) {
            $fp = FirstParty::where('is_active', true)->orderBy('id')->first();
            if ($fp) {
                $data['penandatangan_p1_name']     = $fp->representative_name;
                $data['penandatangan_p1_position'] = $fp->representative_position;
            }
        }

        $contract = ContractDocument::create($data);
        $this->syncEmployeeContractPeriod($contract->employee);

        return redirect()->route('contract-documents.show', $contract)->with('success', 'Dokumen kontrak kerja berhasil dibuat.');
    }

    public function show(ContractDocument $contractDocument)
    {
        $contractDocument->load(['employee.company', 'creator', 'signer']);

        $appLogo    = AppSetting::get('app_logo');
        $appName    = AppSetting::get('app_name', 'LIM Management');
        $appTagline = AppSetting::get('app_tagline', 'Sistem Penggajian');

        return view('contract-documents.show', compact('contractDocument', 'appLogo', 'appName', 'appTagline'));
    }

    public function download(ContractDocument $contractDocument)
    {
        abort_unless($contractDocument->file_path, 404);

        $absolutePath = Storage::disk('private')->path($contractDocument->file_path);
        abort_unless(file_exists($absolutePath), 404);

        return response()->download(
            $absolutePath,
            $contractDocument->original_name ?? basename($absolutePath),
            ['Content-Type' => $contractDocument->mime_type ?? 'application/octet-stream']
        );
    }

    public function edit(ContractDocument $contractDocument)
    {
        abort_if($contractDocument->isSigned(), 422, 'Kontrak yang sudah ditandatangani tidak dapat diedit.');
        $contractDocument->load('employee.company');
        $employees = Employee::with('company')->orderBy('name')->get();
        $firstParties = FirstParty::where('is_active', true)->orderBy('name')->get();
        $contractTemplate = $this->getContractTemplate();

        return view('contract-documents.edit', compact('contractDocument', 'employees', 'firstParties', 'contractTemplate'));
    }

    public function update(Request $request, ContractDocument $contractDocument)
    {
        abort_if($contractDocument->isSigned(), 422, 'Kontrak yang sudah ditandatangani tidak dapat diperbarui.');
        $previousEmployeeId = $contractDocument->employee_id;
        $data = $this->validateContract($request, $contractDocument);
        $data = $this->syncDurationFields($data);

        if (empty($data['contract_number'])) {
            $data['contract_number'] = $contractDocument->contract_number;
        }

        if ($request->hasFile('contract_file')) {
            if ($contractDocument->file_path) {
                Storage::disk('private')->delete($contractDocument->file_path);
            }
            $file = $request->file('contract_file');
            $data['file_path'] = $file->store('contract-documents/' . $data['employee_id'], 'private');
            $data['original_name'] = $file->getClientOriginalName();
            $data['mime_type'] = $file->getMimeType();
            $data['file_size'] = $file->getSize();
        }

        $contractDocument->update($data);
        $contractDocument->load('employee');

        $this->syncEmployeeContractPeriod($contractDocument->employee);

        if ($previousEmployeeId !== $contractDocument->employee_id) {
            $previousEmployee = Employee::find($previousEmployeeId);
            if ($previousEmployee) {
                $this->syncEmployeeContractPeriod($previousEmployee);
            }
        }

        return redirect()->route('contract-documents.show', $contractDocument)->with('success', 'Dokumen kontrak kerja berhasil diperbarui.');
    }

    public function destroy(ContractDocument $contractDocument)
    {
        // Validasi: hanya bisa delete jika belum ditandatangani oleh siapa pun
        abort_if($contractDocument->isCancelled(), 422, 'Kontrak sudah dibatalkan, tidak bisa dihapus.');
        abort_if($contractDocument->isSigned(), 422, 'Kontrak sudah ditandatangani oleh perusahaan, tidak bisa dihapus. Gunakan "Batalkan" jika ingin membatalkan.');
        abort_if($contractDocument->isSignedByEmployee(), 422, 'Kontrak sudah ditandatangani karyawan, tidak bisa dihapus. Gunakan "Batalkan" jika ingin membatalkan.');

        $employee = $contractDocument->employee;
        $contractName = $contractDocument->contract_number ?? 'Kontrak (ID: ' . $contractDocument->id . ')';

        // Delete file jika ada
        if ($contractDocument->file_path) {
            Storage::disk('private')->delete($contractDocument->file_path);
        }

        // Hapus dokumen
        $contractDocument->delete();

        // Resync employee contract period jika ada
        if ($employee) {
            $this->syncEmployeeContractPeriod($employee);
        }

        return redirect()->route('contract-documents.index')
            ->with('success', "Dokumen kontrak \"$contractName\" berhasil dihapus permanen.");
    }

    public function cancel(Request $request, ContractDocument $contractDocument)
    {
        $request->validate(['reason' => 'nullable|string|max:500']);

        $contractDocument->update([
            'status'           => 'cancelled',
            'rejection_reason' => $request->input('reason'),
            'rejected_at'      => now(),
            'rejected_by'      => Auth::id(),
        ]);

        $this->syncEmployeeContractPeriod($contractDocument->employee);

        return redirect()->back()->with('success', 'Kontrak berhasil dibatalkan.');
    }

    public function sign(ContractDocument $contractDocument)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->canSign(), 403, 'Anda tidak memiliki izin untuk menandatangani kontrak.');
        abort_if($contractDocument->isSigned(), 422, 'Kontrak ini sudah ditandatangani.');

        $signatureNumber = $this->generateSignatureNumber($contractDocument, $user);
        $qrPayload = json_encode([
            'type' => 'contract-signature',
            'signature_number' => $signatureNumber,
            'contract_id' => $contractDocument->id,
            'contract_number' => $contractDocument->contract_number,
            'signer_id' => $user->id,
            'signer_name' => $user->name,
            'signed_at' => now()->toIso8601String(),
        ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $qrResult = (new Builder(new SvgWriter()))
            ->build(
                data: $qrPayload,
                encoding: new Encoding('UTF-8'),
                errorCorrectionLevel: ErrorCorrectionLevel::High,
                size: 220,
                margin: 8,
            );

        $contractDocument->update([
            'signed_by' => Auth::id(),
            'signed_at' => now(),
            'signature_number' => $signatureNumber,
            'signature_qr_data_uri' => $qrResult->getDataUri(),
        ]);

        return redirect()->back()->with('success', 'Kontrak berhasil ditandatangani digital.');
    }

    public function unsign(ContractDocument $contractDocument)
    {
        $user = Auth::user();
        abort_unless($user instanceof User && $user->canSign(), 403, 'Anda tidak memiliki izin untuk membatalkan tanda tangan kontrak.');
        abort_unless($contractDocument->isSigned(), 422, 'Kontrak ini belum ditandatangani.');

        $contractDocument->update([
            'signed_by' => null,
            'signed_at' => null,
            'signature_number' => null,
            'signature_qr_data_uri' => null,
        ]);

        return redirect()->back()->with('success', 'Tanda tangan kontrak berhasil dibatalkan.');
    }

    public function saveTemplateFromCreate(Request $request)
    {
        $rules = [];
        foreach (self::TEMPLATE_FIELDS as $field) {
            $rules[$field] = 'nullable|string';
        }

        $data = $request->validate($rules);

        foreach (self::TEMPLATE_FIELDS as $field) {
            AppSetting::set('contract_template_' . $field, $data[$field] ?? null);
        }

        return back()->with('success', 'Template kontrak berhasil disimpan dari form create.');
    }

    public function supportingData(Employee $employee)
    {
        $employee->load('company');

        $requiredTypes = EmployeeDocument::contractTypeOptions();
        $requiredKeys = array_keys($requiredTypes);

        $latestDocuments = EmployeeDocument::query()
            ->where('employee_id', $employee->id)
            ->whereIn('document_type', $requiredKeys)
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('document_type')
            ->map(fn ($docs) => $docs->first());

        $documents = [];
        $missingDocumentLabels = [];
        foreach ($requiredTypes as $type => $label) {
            $doc = $latestDocuments->get($type);
            $available = $doc !== null;

            if (!$available) {
                $missingDocumentLabels[] = $label;
            }

            $documents[] = [
                'type' => $type,
                'label' => $label,
                'available' => $available,
                'document_label' => $doc?->label,
                'uploaded_at' => $doc?->created_at?->toIso8601String(),
            ];
        }

        $missingDataFields = [];
        if (blank($employee->npwp)) {
            $missingDataFields[] = 'NPWP karyawan';
        }
        if (blank($employee->bank_name) || blank($employee->bank_account)) {
            $missingDataFields[] = 'Data bank karyawan';
        }

        $hasMissingSupportingData = !empty($missingDocumentLabels) || !empty($missingDataFields);

        return response()->json([
            'employee' => [
                'id' => $employee->id,
                'employee_id' => $employee->employee_id,
                'name' => $employee->name,
                'company_name' => $employee->company?->name,
                'position' => $employee->position,
                'npwp' => $employee->npwp,
                'bank_name' => $employee->bank_name,
                'bank_account' => $employee->bank_account,
            ],
            'documents' => $documents,
            'missing_document_labels' => $missingDocumentLabels,
            'missing_data_fields' => $missingDataFields,
            'has_missing_supporting_data' => $hasMissingSupportingData,
            'info_message' => $hasMissingSupportingData
                ? 'Data pendukung belum lengkap untuk karyawan ini. Lengkapi data karyawan/dokumen pendukung terlebih dahulu.'
                : 'Data pendukung karyawan tersedia dan siap dipakai untuk pembuatan kontrak.',
        ]);
    }

    private function validateContract(Request $request, ?ContractDocument $existing = null): array
    {
        return $request->validate([
            'employee_id' => 'required|exists:employees,id',
            'contract_number' => [
                'nullable',
                'string',
                'max:100',
                Rule::unique('contract_documents', 'contract_number')->ignore($existing?->id),
            ],
            'contract_date' => 'nullable|date',
            'location' => 'nullable|string|max:255',
            'first_party_name' => 'nullable|string|max:255',
            'first_party_position' => 'nullable|string|max:255',
            'first_party_company' => 'nullable|string|max:255',
            'first_party_address' => 'nullable|string',
            'penandatangan_p1_name'     => 'nullable|string|max:255',
            'penandatangan_p1_position' => 'nullable|string|max:255',
            'second_party_name' => 'nullable|string|max:255',
            'second_party_address' => 'nullable|string',
            'second_party_ktp' => 'nullable|string|max:100',
            'project_name' => 'nullable|string|max:255',
            'scope_of_work' => 'nullable|string',
            'duration_text' => 'nullable|string|max:255',
            'start_date' => 'nullable|date',
            'end_date' => 'nullable|date|after_or_equal:start_date',
            'contract_value' => 'nullable|numeric|min:0',
            'contract_value_text' => 'nullable|string|max:255',
            'base_salary' => 'nullable|numeric|min:0',
            'salary_components' => 'nullable|array',
            'salary_components.*.label' => 'nullable|string|max:100',
            'salary_components.*.type' => 'nullable|in:income,deduction',
            'salary_components.*.amount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string|max:100',
            'payment_terms' => 'nullable|string',
            'rights_obligations' => 'nullable|string',
            'hki_terms' => 'nullable|string',
            'nda_terms' => 'nullable|string',
            'sanctions_terms' => 'nullable|string',
            'dispute_terms' => 'nullable|string',
            'bank_name' => 'nullable|string|max:255',
            'bank_account' => 'nullable|string|max:255',
            'bank_account_name' => 'nullable|string|max:255',
            'notes' => 'nullable|string',
            'contract_file' => 'nullable|file|mimes:pdf,jpg,jpeg,png,webp,doc,docx|max:10240',
        ]);
    }

    private function generateContractNumber(): string
    {
        $month = now()->format('m');
        $year = now()->format('Y');

        // Guard when migration has not been applied yet.
        if (!Schema::hasTable('contract_documents') || !Schema::hasColumn('contract_documents', 'contract_number')) {
            return '234/SPK/LIM/' . $month . '/' . $year;
        }

        $latest = ContractDocument::whereNotNull('contract_number')
            ->where('contract_number', 'like', '%/SPK/LIM/' . $month . '/' . $year)
            ->orderByDesc('id')
            ->value('contract_number');

        $next = 234;
        if ($latest && preg_match('/^(\d{3})\/SPK\/LIM\/' . $month . '\/' . $year . '$/', $latest, $m)) {
            $next = max(234, ((int) $m[1]) + 1);
        }

        return str_pad((string) $next, 3, '0', STR_PAD_LEFT) . '/SPK/LIM/' . $month . '/' . $year;
    }

    private function syncDurationFields(array $data): array
    {
        $start = !empty($data['start_date']) ? Carbon::parse($data['start_date'])->startOfDay() : null;
        $end = !empty($data['end_date']) ? Carbon::parse($data['end_date'])->startOfDay() : null;
        $durationText = trim((string) ($data['duration_text'] ?? ''));

        if ($start && !$end && $durationText !== '') {
            if (preg_match('/^(\d+)\s*(hari|bulan|tahun)$/i', $durationText, $m)) {
                $value = (int) $m[1];
                $unit = strtolower($m[2]);
                $calculatedEnd = $start->copy();

                if ($unit === 'hari') {
                    $calculatedEnd->addDays($value);
                } elseif ($unit === 'bulan') {
                    $calculatedEnd->addMonthsNoOverflow($value);
                } elseif ($unit === 'tahun') {
                    $calculatedEnd->addYearsNoOverflow($value);
                }

                $data['end_date'] = $calculatedEnd->toDateString();
                $end = $calculatedEnd;
            }
        }

        if ($start && $end && $end->gte($start)) {
            $diff = $start->diff($end);
            $parts = [];
            if ($diff->y > 0) $parts[] = $diff->y . ' tahun';
            if ($diff->m > 0) $parts[] = $diff->m . ' bulan';
            if ($diff->d > 0) $parts[] = $diff->d . ' hari';
            $data['duration_text'] = !empty($parts) ? implode(' ', $parts) : '0 hari';
        }

        return $data;
    }

    private function getContractTemplate(): array
    {
        // Auto-fill first-party fields from master data if not yet saved in AppSettings.
        $defaultFirstParty = FirstParty::where('is_active', true)
            ->orderBy('id')
            ->first();

        $defaults = [
            'location' => '',
            'project_name' => '',
            'first_party_name' => $defaultFirstParty?->representative_name ?? '',
            'first_party_position' => $defaultFirstParty?->representative_position ?? '',
            'first_party_company' => $defaultFirstParty?->name ?? '',
            'first_party_address' => $defaultFirstParty?->address ?? '',
            'second_party_name' => '',
            'second_party_address' => '',
            'second_party_ktp' => '',
            'duration_text' => '',
            'payment_method' => 'Lump Sum',
            'contract_value_text' => '',
            'contract_value' => '',
            'payment_terms' => "Pembayaran dilakukan secara penuh (Lump Sum) setelah seluruh pekerjaan diselesaikan dan Berita Acara Serah Terima (BAST) ditandatangani oleh kedua belah pihak.\n\nApabila disepakati pembayaran bertahap, maka termin dan persentase pembayaran akan dicantumkan dalam Lampiran Perjanjian ini yang merupakan satu kesatuan tak terpisahkan.",
            'scope_of_work' => "PIHAK KEDUA wajib melaksanakan pekerjaan sebagaimana tercantum dalam Pasal 1 Perjanjian ini, meliputi:\n\n1. [Uraian lingkup pekerjaan utama]\n2. [Uraian lingkup pekerjaan tambahan jika ada]\n3. Menyerahkan seluruh hasil pekerjaan beserta dokumentasi teknis yang diperlukan kepada PIHAK PERTAMA.\n4. Mematuhi arahan dan perubahan pekerjaan yang disampaikan PIHAK PERTAMA sepanjang masih dalam ruang lingkup perjanjian ini.",
            'rights_obligations' => "Hak PIHAK PERTAMA:\n1. Menerima hasil pekerjaan sesuai spesifikasi, kualitas, dan tenggat waktu yang telah disepakati.\n2. Memberikan evaluasi dan meminta perbaikan atas hasil pekerjaan yang tidak memenuhi standar yang ditentukan.\n\nKewajiban PIHAK PERTAMA:\n1. Menyediakan data, informasi, dan akses yang diperlukan PIHAK KEDUA untuk pelaksanaan pekerjaan.\n2. Melakukan pembayaran sesuai nilai dan termin yang telah disepakati.\n\nHak PIHAK KEDUA:\n1. Menerima pembayaran tepat waktu sesuai perjanjian.\n2. Mendapatkan kejelasan atas spesifikasi dan arahan pekerjaan.\n\nKewajiban PIHAK KEDUA:\n1. Melaksanakan pekerjaan secara profesional, tepat waktu, dan sesuai standar yang disepakati.\n2. Menjaga kerahasiaan informasi PIHAK PERTAMA selama dan setelah masa perjanjian.",
            'hki_terms' => "Seluruh hasil karya, dokumen, kode sumber (source code), desain, dan materi lainnya yang dihasilkan PIHAK KEDUA dalam pelaksanaan pekerjaan berdasarkan perjanjian ini merupakan hak milik eksklusif PIHAK PERTAMA sejak diserahkan dan lunas dibayar.\n\nPIHAK KEDUA dengan ini mengalihkan seluruh hak kekayaan intelektual atas hasil pekerjaan tersebut kepada PIHAK PERTAMA dan tidak berhak menggunakannya untuk kepentingan pihak lain tanpa persetujuan tertulis dari PIHAK PERTAMA.",
            'nda_terms' => "PIHAK KEDUA wajib menjaga kerahasiaan seluruh informasi, data, dokumen, strategi bisnis, dan hal-hal lain yang diperoleh dalam rangka pelaksanaan perjanjian ini, baik selama masa perjanjian maupun setelah perjanjian berakhir.\n\nKewajiban kerahasiaan ini tidak berlaku terhadap informasi yang:\n1. Telah diketahui publik bukan karena kelalaian PIHAK KEDUA.\n2. Wajib diungkapkan berdasarkan ketentuan peraturan perundang-undangan yang berlaku.",
            'sanctions_terms' => "Perjanjian ini berakhir apabila:\n1. Jangka waktu perjanjian telah habis dan tidak diperpanjang.\n2. Salah satu pihak mengajukan pemutusan secara tertulis dengan pemberitahuan minimal 30 (tiga puluh) hari kalender sebelumnya.\n\nPIHAK PERTAMA berhak mengakhiri perjanjian secara sepihak dan seketika tanpa pemberitahuan terlebih dahulu apabila PIHAK KEDUA:\n1. Terbukti melakukan kecurangan, penggelapan, atau pelanggaran hukum.\n2. Melanggar kewajiban kerahasiaan yang diatur dalam perjanjian ini.\n\nApabila PIHAK KEDUA terlambat menyelesaikan pekerjaan, dikenakan denda keterlambatan sebesar 0,1% (nol koma satu persen) per hari dari sisa nilai kontrak, maksimal sebesar 5% (lima persen).",
            'dispute_terms' => "Setiap perselisihan yang timbul sehubungan dengan perjanjian ini akan diselesaikan terlebih dahulu melalui musyawarah untuk mufakat antara PARA PIHAK dalam jangka waktu 30 (tiga puluh) hari kalender sejak perselisihan disampaikan secara tertulis.\n\nApabila musyawarah tidak menghasilkan kesepakatan, PARA PIHAK sepakat untuk menyelesaikan perselisihan melalui Pengadilan Negeri yang berwenang sesuai peraturan perundang-undangan yang berlaku di Republik Indonesia.",
            'bank_name' => '',
            'bank_account' => '',
            'bank_account_name' => '',
            'notes' => "Pasal 9 — Ketentuan Lain-lain:\nHal-hal yang belum diatur atau yang memerlukan penyesuaian dalam perjanjian ini akan ditetapkan berdasarkan kesepakatan tertulis PARA PIHAK sebagai addendum yang merupakan bagian tidak terpisahkan dari perjanjian ini.\n\nPasal 10 — Penutup:\nPerjanjian ini dibuat dalam keadaan sadar, tanpa paksaan dari pihak mana pun, dan ditandatangani dalam 2 (dua) rangkap bermaterai cukup, masing-masing mempunyai kekuatan hukum yang sama.",
        ];

        $template = [];
        foreach ($defaults as $key => $fallback) {
            $template[$key] = AppSetting::get('contract_template_' . $key, $fallback) ?? $fallback;
        }

        return $template;
    }

    private function generateSignatureNumber(ContractDocument $contractDocument, User $user): string
    {
        $datePart = now()->format('YmdHis');
        $randomPart = Str::upper(Str::random(6));

        return sprintf('SGN-%s-C%s-U%s-%s', $datePart, $contractDocument->id, $user->id, $randomPart);
    }

    private function syncEmployeeContractPeriod(?Employee $employee): void
    {
        if (!$employee) {
            return;
        }

        $mainContract = $employee->contractDocuments()
            ->orderByDesc('contract_date')
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->first();

        $updates = [
            'contract_start' => $mainContract?->start_date?->toDateString(),
            'contract_end'   => $mainContract?->end_date?->toDateString(),
        ];

        // Sync salary from main contract if set
        if ($mainContract && ($mainContract->base_salary ?? 0) > 0) {
            $updates['base_salary']        = $mainContract->base_salary;
            $updates['salary_components']  = $this->normalizeSalaryComponents(
                (array) ($mainContract->salary_components ?? [])
            );
        }

        $employee->update($updates);
    }

    private function normalizeSalaryComponents(array $rows): array
    {
        $out = [];
        foreach ($rows as $r) {
            $label  = trim((string) ($r['label']  ?? ''));
            $amount = (float) ($r['amount'] ?? 0);
            $type   = ($r['type'] ?? 'income') === 'deduction' ? 'deduction' : 'income';
            if ($label === '' || $amount <= 0) continue;
            $out[] = ['label' => $label, 'type' => $type, 'amount' => $amount];
        }
        return $out;
    }
}