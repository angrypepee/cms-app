<?php

namespace App\Http\Controllers;

use App\Models\AppSetting;
use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CmsController extends Controller
{
    private const CONTRACT_TEMPLATE_FIELDS = [
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

    public function index()
    {
        $appLogo  = AppSetting::get('app_logo');
        $appName  = AppSetting::get('app_name', 'LIM Management');
        $appTagline = AppSetting::get('app_tagline', 'Sistem Penggajian');
        $companies = Company::orderBy('name')->get();
        $contractTemplate = $this->loadContractTemplate();
        $githubToken = AppSetting::get('github_token', '');
        $gitlabToken = AppSetting::get('gitlab_token', '');

        return view('cms.index', compact('appLogo', 'appName', 'appTagline', 'companies', 'contractTemplate', 'githubToken', 'gitlabToken'));
    }

    public function updateAppBranding(Request $request)
    {
        $validated = $request->validate([
            'app_name'    => 'nullable|string|max:100',
            'app_tagline' => 'nullable|string|max:150',
            'app_logo'    => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:1024',
            'remove_logo' => 'nullable|boolean',
        ]);

        AppSetting::set('app_name', $validated['app_name'] ?? 'LIM Management');
        AppSetting::set('app_tagline', $validated['app_tagline'] ?? 'Sistem Penggajian');

        if ($request->boolean('remove_logo')) {
            $old = AppSetting::get('app_logo');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            AppSetting::set('app_logo', null);
            return back()->with('success', 'Logo aplikasi dihapus.');
        }

        if ($request->hasFile('app_logo')) {
            $old = AppSetting::get('app_logo');
            if ($old) {
                Storage::disk('public')->delete($old);
            }
            $path = $request->file('app_logo')->store('branding', 'public');
            AppSetting::set('app_logo', $path);
        }

        return back()->with('success', 'Pengaturan branding aplikasi diperbarui.');
    }

    public function updateCompanyLogo(Request $request, Company $company)
    {
        $request->validate([
            'logo'        => 'nullable|image|mimes:jpg,jpeg,png,svg,webp|max:1024',
            'remove_logo' => 'nullable|boolean',
        ]);

        if ($request->boolean('remove_logo')) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
                $company->update(['logo' => null]);
            }
            return back()->with('success', "Logo {$company->name} dihapus.");
        }

        if ($request->hasFile('logo')) {
            if ($company->logo) {
                Storage::disk('public')->delete($company->logo);
            }
            $path = $request->file('logo')->store('logos', 'public');
            $company->update(['logo' => $path]);
            return back()->with('success', "Logo {$company->name} diperbarui. Slip gaji akan menggunakan logo baru.");
        }

        return back()->with('warning', 'Tidak ada perubahan.');
    }

    public function updateContractTemplate(Request $request)
    {
        $rules = [];
        foreach (self::CONTRACT_TEMPLATE_FIELDS as $field) {
            $rules[$field] = 'nullable|string';
        }

        $validated = $request->validate($rules);

        foreach (self::CONTRACT_TEMPLATE_FIELDS as $field) {
            AppSetting::set('contract_template_' . $field, $validated[$field] ?? null);
        }

        return back()->with('success', 'Template kontrak berhasil diperbarui.');
    }

    public function updateRepoTokens(Request $request)
    {
        // Only update if field is not the mask placeholder
        $github = $request->input('github_token', '');
        $gitlab = $request->input('gitlab_token', '');

        if ($github && !str_starts_with($github, '•')) {
            AppSetting::set('github_token', $github);
        }
        if ($gitlab && !str_starts_with($gitlab, '•')) {
            AppSetting::set('gitlab_token', $gitlab);
        }

        return back()->with('success', 'Token integrasi repo berhasil disimpan.');
    }

    private function loadContractTemplate(): array
    {
        $defaultFirstParty = \App\Models\FirstParty::where('is_active', true)
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
        foreach ($defaults as $field => $fallback) {
            $template[$field] = old($field, AppSetting::get('contract_template_' . $field, $fallback) ?? $fallback);
        }

        return $template;
    }
}
