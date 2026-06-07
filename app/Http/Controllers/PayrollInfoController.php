<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use App\Models\EmployeeDocument;
use App\Models\PayrollItem;
use App\Models\PayrollSlip;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PayrollInfoController extends Controller
{
    const PAYROLL_DAY = 25;

    public function index(Request $request)
    {
        return view('payroll-info.index', $this->buildPayrollContext($request, 'Informasi Penggajian'));
    }

    public function contractIndex(Request $request)
    {
        return view('kontrak-kerja.index', $this->buildPayrollContext($request, 'Kontrak Kerja'));
    }

    public function contractShow(Employee $employee)
    {
        $employee->load(['company', 'documents.uploader']);
        $documentTypes = EmployeeDocument::contractTypeOptions();
        $documentsByType = $employee->documents->groupBy('document_type');

        return view('kontrak-kerja.show', compact('employee', 'documentTypes', 'documentsByType'));
    }

    private function buildPayrollContext(Request $request, string $pageTitle): array
    {
        $today = now()->startOfDay();
        $day   = self::PAYROLL_DAY;

        $nextPayroll = $today->day <= $day
            ? $today->copy()->setDay($day)
            : $today->copy()->addMonthNoOverflow()->setDay($day);

        $prevPayroll = $today->day >= $day
            ? $today->copy()->setDay($day)
            : $today->copy()->subMonthNoOverflow()->setDay($day);

        $daysUntil = (int) $today->diffInDays($nextPayroll, false);

        $companies = Company::orderBy('name')->pluck('name', 'id');

        $allActive     = Employee::where('is_active', true)->count();
        $expiredCount  = Employee::where('is_active', true)
            ->whereNotNull('contract_end')
            ->where('contract_end', '<', $today->toDateString())
            ->count();
        $expiringCount = Employee::where('is_active', true)
            ->whereNotNull('contract_end')
            ->whereBetween('contract_end', [$today->toDateString(), $today->copy()->addDays(30)->toDateString()])
            ->count();

        // Current payroll period = month of next-payroll date
        $periodMonth = $nextPayroll->month;
        $periodYear  = $nextPayroll->year;

        $paidIds = PayrollSlip::where('period_month', $periodMonth)
            ->where('period_year', $periodYear)
            ->pluck('employee_id')
            ->all();

        $employees = Employee::with('company')
            ->where('is_active', true)
            ->when($request->company_id, fn($q) => $q->where('company_id', $request->company_id))
            ->when($request->search, fn($q) => $q->where('name', 'like', '%' . $request->search . '%'))
            ->when($request->contract_status, function ($q) use ($request, $today) {
                match ($request->contract_status) {
                    'expired'  => $q->whereNotNull('contract_end')->where('contract_end', '<', $today->toDateString()),
                    'expiring' => $q->whereNotNull('contract_end')->whereBetween('contract_end', [
                        $today->toDateString(),
                        $today->copy()->addDays(30)->toDateString(),
                    ]),
                    'active'   => $q->where(fn($q2) => $q2
                        ->whereNull('contract_end')
                        ->orWhere('contract_end', '>', $today->copy()->addDays(30)->toDateString())
                    ),
                    default => $q,
                };
            })
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $paidCount   = count($paidIds);
        $unpaidCount = Employee::where('is_active', true)
            ->whereNotIn('id', $paidIds ?: [0])
            ->whereNotNull('base_salary')
            ->where('base_salary', '>', 0)
            ->count();

        return compact(
            'employees', 'nextPayroll', 'prevPayroll',
            'daysUntil', 'companies', 'today',
            'allActive', 'expiredCount', 'expiringCount',
            'paidIds', 'periodMonth', 'periodYear',
            'paidCount', 'unpaidCount', 'pageTitle'
        );
    }

    /** Transfer payroll for ONE employee → auto-generate published slip + proof. */
    public function transfer(Request $request, Employee $employee)
    {
        [$periodMonth, $periodYear] = $this->resolvePeriod($request);

        $validated = $request->validate([
            'transfer_reference'  => 'required|string|max:100',
            'transfer_bank'       => 'required|string|max:100',
            'transfer_proof'      => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:5120',
            'transfer_notes'      => 'nullable|string|max:1000',
        ], [
            'transfer_reference.required' => 'Nomor transaksi wajib diisi.',
            'transfer_bank.required'      => 'Bank pengirim wajib diisi.',
            'transfer_proof.mimes'        => 'Bukti transfer harus berupa file JPG, PNG, atau PDF.',
            'transfer_proof.max'          => 'Ukuran file bukti maksimal 5 MB.',
        ]);

        if (!$employee->is_active) {
            return back()->with('error', "Karyawan {$employee->name} tidak aktif.");
        }
        if (!$employee->base_salary || $employee->base_salary <= 0) {
            return back()->with('error', "Gaji pokok {$employee->name} belum diatur. Silakan edit data karyawan terlebih dahulu.");
        }

        $exists = PayrollSlip::where('employee_id', $employee->id)
            ->where('period_month', $periodMonth)
            ->where('period_year', $periodYear)
            ->first();

        if ($exists) {
            return back()->with('warning', "Slip gaji {$employee->name} untuk periode ini sudah ada (#{$exists->slip_number}).");
        }

        $proofPath = null;
        if ($request->hasFile('transfer_proof')) {
            $proofPath = $request->file('transfer_proof')->store('transfer-proofs', 'public');
        }

        $slip = $this->generateSlipForEmployee($employee, $periodMonth, $periodYear, [
            'transfer_reference'   => $validated['transfer_reference'],
            'transfer_bank'        => $validated['transfer_bank'],
            'transfer_proof_path'  => $proofPath,
            'transfer_notes'       => $validated['transfer_notes'] ?? null,
            'transferred_at'       => now(),
            'transferred_by'       => auth()->id(),
        ]);

        return back()->with('success', "Transfer berhasil. Slip gaji #{$slip->slip_number} untuk {$employee->name} dibuat otomatis dengan bukti transfer.");
    }

    /** Transfer payroll for ALL active employees with base_salary set. */
    public function transferAll(Request $request)
    {
        [$periodMonth, $periodYear] = $this->resolvePeriod($request);

        $existingIds = PayrollSlip::where('period_month', $periodMonth)
            ->where('period_year', $periodYear)
            ->pluck('employee_id')
            ->all();

        $employees = Employee::where('is_active', true)
            ->whereNotIn('id', $existingIds ?: [0])
            ->whereNotNull('base_salary')
            ->where('base_salary', '>', 0)
            ->get();

        $created = 0;
        DB::transaction(function () use ($employees, $periodMonth, $periodYear, &$created) {
            foreach ($employees as $emp) {
                $this->generateSlipForEmployee($emp, $periodMonth, $periodYear);
                $created++;
            }
        });

        if ($created === 0) {
            return back()->with('warning', 'Tidak ada slip baru dibuat. Semua karyawan sudah memiliki slip untuk periode ini, atau gaji pokok belum diatur.');
        }

        return back()->with('success', "Berhasil men-transfer & membuat {$created} slip gaji untuk periode {$periodMonth}/{$periodYear}.");
    }

    /**
     * Admin-only: Bulk update the signature date (admin and/or employee) on
     * payroll slips for the given period — or across ALL periods.
     */
    public function updateSignDate(Request $request)
    {
        abort_unless(auth()->user()?->isAdmin(), 403, 'Hanya administrator yang dapat mengubah tanggal tanda tangan.');

        $data = $request->validate([
            'sign_date'   => 'required|date',
            'targets'     => 'required|array|min:1',
            'targets.*'   => 'in:admin,employee',
            'scope'       => 'required|in:period,all',
            'period_month'=> 'nullable|integer|min:1|max:12',
            'period_year' => 'nullable|integer|min:2000|max:2100',
        ], [
            'targets.required' => 'Pilih minimal satu jenis tanda tangan (admin atau karyawan).',
        ]);

        $newDate = Carbon::parse($data['sign_date']);

        $query = PayrollSlip::query();

        if ($data['scope'] === 'period') {
            [$periodMonth, $periodYear] = $this->resolvePeriod($request);
            $query->where('period_month', $periodMonth)
                  ->where('period_year', $periodYear);
            $scopeLabel = "periode {$periodMonth}/{$periodYear}";
        } else {
            $scopeLabel = 'semua periode';
        }

        $targets = $data['targets'];
        $updateAdmin    = in_array('admin', $targets, true);
        $updateEmployee = in_array('employee', $targets, true);

        // Only touch rows that actually have the corresponding signature set,
        // so we don't accidentally backdate unsigned slips.
        $adminUpdated = 0;
        $empUpdated   = 0;

        DB::transaction(function () use ($query, $newDate, $updateAdmin, $updateEmployee, &$adminUpdated, &$empUpdated) {
            if ($updateAdmin) {
                $adminUpdated = (clone $query)
                    ->whereNotNull('signed_at')
                    ->update(['signed_at' => $newDate]);
            }
            if ($updateEmployee) {
                $empUpdated = (clone $query)
                    ->whereNotNull('employee_signed_at')
                    ->update(['employee_signed_at' => $newDate]);
            }
        });

        $totalSlips = $adminUpdated + $empUpdated;
        if ($totalSlips === 0) {
            return back()->with('warning', "Tidak ada slip pada {$scopeLabel} yang memiliki tanda tangan untuk diperbarui.");
        }

        $parts = [];
        if ($updateAdmin)    $parts[] = "{$adminUpdated} tanda tangan admin";
        if ($updateEmployee) $parts[] = "{$empUpdated} tanda tangan karyawan";

        return back()->with('success', sprintf(
            'Berhasil memperbarui %s pada %s ke tanggal %s.',
            implode(' & ', $parts),
            $scopeLabel,
            $newDate->format('d M Y H:i')
        ));
    }

    private function resolvePeriod(Request $request): array
    {
        $today = now();
        $day   = self::PAYROLL_DAY;
        $nextPayroll = $today->day <= $day
            ? $today->copy()->setDay($day)
            : $today->copy()->addMonthNoOverflow()->setDay($day);

        return [
            (int) ($request->period_month ?? $nextPayroll->month),
            (int) ($request->period_year  ?? $nextPayroll->year),
        ];
    }

    /**
     * Monthly payment report — company-wide summary of payroll for a given period.
     * Aggregates all generated slips (already auto-built from each employee's
     * salary agreement) into a single payment information sheet.
     */
    public function report(Request $request)
    {
        [$periodMonth, $periodYear] = $this->resolvePeriod($request);

        $slips = PayrollSlip::with(['employee.company', 'items'])
            ->where('period_month', $periodMonth)
            ->where('period_year', $periodYear)
            ->orderBy('employee_id')
            ->get();

        $totals = [
            'employees' => $slips->count(),
            'income'    => (float) $slips->sum('total_income'),
            'deduction' => (float) $slips->sum('total_deduction'),
            'take_home' => (float) $slips->sum('take_home_pay'),
            'signed_admin'    => $slips->whereNotNull('signed_at')->count(),
            'signed_employee' => $slips->whereNotNull('employee_signed_at')->count(),
        ];

        // Per-company breakdown
        $byCompany = $slips->groupBy(fn($s) => $s->employee?->company?->name ?? '—')
            ->map(fn($g) => [
                'count'     => $g->count(),
                'income'    => (float) $g->sum('total_income'),
                'deduction' => (float) $g->sum('total_deduction'),
                'take_home' => (float) $g->sum('take_home_pay'),
            ]);

        // Available periods (for the selector)
        $availablePeriods = PayrollSlip::select('period_month', 'period_year')
            ->groupBy('period_month', 'period_year')
            ->orderByDesc('period_year')->orderByDesc('period_month')
            ->get();

        $monthsId = [1=>'Januari',2=>'Februari',3=>'Maret',4=>'April',5=>'Mei',6=>'Juni',7=>'Juli',8=>'Agustus',9=>'September',10=>'Oktober',11=>'November',12=>'Desember'];

        return view('payroll-info.report', compact(
            'slips', 'totals', 'byCompany', 'periodMonth', 'periodYear',
            'availablePeriods', 'monthsId'
        ));
    }

    private function generateSlipForEmployee(Employee $employee, int $periodMonth, int $periodYear, array $transferData = []): PayrollSlip
    {
        $paymentDate = Carbon::create($periodYear, $periodMonth, self::PAYROLL_DAY);
        $cutoffEnd   = $paymentDate->copy();
        $cutoffStart = $cutoffEnd->copy()->subMonthNoOverflow()->addDay();

        $agreement   = $employee->salaryAgreement();   // includes Gaji Pokok + components
        $totals      = $employee->agreementTotals();

        return DB::transaction(function () use ($employee, $periodMonth, $periodYear, $paymentDate, $cutoffStart, $cutoffEnd, $agreement, $totals, $transferData) {
            $slip = PayrollSlip::create(array_merge([
                'slip_number'     => PayrollSlip::generateSlipNumber($periodYear, $periodMonth),
                'company_id'      => $employee->company_id,
                'employee_id'     => $employee->id,
                'period_month'    => $periodMonth,
                'period_year'     => $periodYear,
                'cutoff_start'    => $cutoffStart->toDateString(),
                'cutoff_end'      => $cutoffEnd->toDateString(),
                'payment_date'    => $paymentDate->toDateString(),
                'total_income'    => $totals['income'],
                'total_deduction' => $totals['deduction'],
                'take_home_pay'   => $totals['take_home'],
                'notes'           => 'Auto-generated dari kesepakatan gaji karyawan.',
                'status'          => 'published',
            ], $transferData));

            $slip->applyDefaultSignatures()->save();

            $incomeIdx = 0; $deductionIdx = 0;
            foreach ($agreement as $row) {
                PayrollItem::create([
                    'payroll_slip_id' => $slip->id,
                    'type'            => $row['type'],
                    'label'           => $row['label'],
                    'amount'          => $row['amount'],
                    'sort_order'      => $row['type'] === 'income' ? $incomeIdx++ : $deductionIdx++,
                ]);
            }

            return $slip;
        });
    }
}
