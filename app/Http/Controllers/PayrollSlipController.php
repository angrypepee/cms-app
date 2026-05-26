<?php

namespace App\Http\Controllers;

use App\Models\Company;
use App\Models\Employee;
use App\Models\PayrollSlip;
use App\Models\PayrollItem;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PayrollSlipController extends Controller
{
    public function index(Request $request)
    {
        $query = PayrollSlip::with(['employee', 'company']);

        if ($request->filled('search')) {
            $q = $request->search;
            $query->whereHas('employee', fn ($b) => $b->where('name', 'like', "%$q%")
                ->orWhere('employee_id', 'like', "%$q%"))
                ->orWhere('slip_number', 'like', "%$q%");
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('month') && $request->filled('year')) {
            $query->where('period_month', $request->month)->where('period_year', $request->year);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $slips     = $query->latest()->paginate(15)->withQueryString();
        $companies = Company::orderBy('name')->get();

        return view('payroll-slips.index', compact('slips', 'companies'));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        return view('payroll-slips.create', compact('companies'));
    }

    /**
     * Show form to generate slips for many employees at once based on a month/year.
     */
    public function bulkCreate(Request $request)
    {
        $companies = Company::orderBy('name')->get();

        $companyId = $request->integer('company_id') ?: optional($companies->first())->id;
        $month     = $request->integer('month') ?: (int) now()->format('n');
        $year      = $request->integer('year')  ?: (int) now()->format('Y');

        $employees = collect();
        $existingEmployeeIds = collect();

        if ($companyId) {
            $employees = Employee::where('company_id', $companyId)
                ->where('is_active', true)
                ->orderBy('name')
                ->get();

            $existingEmployeeIds = PayrollSlip::where('company_id', $companyId)
                ->where('period_month', $month)
                ->where('period_year', $year)
                ->pluck('employee_id');
        }

        return view('payroll-slips.bulk-create', compact(
            'companies', 'employees', 'companyId', 'month', 'year', 'existingEmployeeIds'
        ));
    }

    /**
     * Persist multiple slips at once. Slips use each employee's salary agreement
     * (base_salary + salary_components). Employees already having a slip for the
     * same company/month/year are skipped.
     */
    public function bulkStore(Request $request)
    {
        $validated = $request->validate([
            'company_id'   => 'required|exists:companies,id',
            'period_month' => 'required|integer|between:1,12',
            'period_year'  => 'required|integer|min:2000|max:2099',
            'payment_date' => 'nullable|date',
            'released_at'  => 'nullable|date',
            'cutoff_start' => 'nullable|date',
            'cutoff_end'   => 'nullable|date|after_or_equal:cutoff_start',
            'employee_ids'   => 'required|array|min:1',
            'employee_ids.*' => 'integer|exists:employees,id',
            'action'         => 'nullable|in:draft,publish',
        ]);

        $status = ($validated['action'] ?? 'draft') === 'publish' ? 'published' : 'draft';

        $created = 0;
        $skippedExisting = 0;
        $skippedNoSalary = 0;

        DB::transaction(function () use ($validated, $status, &$created, &$skippedExisting, &$skippedNoSalary) {
            $employees = Employee::where('company_id', $validated['company_id'])
                ->whereIn('id', $validated['employee_ids'])
                ->get();

            foreach ($employees as $employee) {
                // Skip duplicates for the same period
                $exists = PayrollSlip::where('company_id', $validated['company_id'])
                    ->where('employee_id', $employee->id)
                    ->where('period_month', $validated['period_month'])
                    ->where('period_year', $validated['period_year'])
                    ->exists();

                if ($exists) {
                    $skippedExisting++;
                    continue;
                }

                $rows = $employee->salaryAgreement();
                // Filter out empty base salary rows (no salary configured at all)
                $usableRows = array_values(array_filter($rows, fn ($r) => (float) $r['amount'] > 0));
                if (empty($usableRows)) {
                    $skippedNoSalary++;
                    continue;
                }

                $totalIncome    = 0;
                $totalDeduction = 0;
                foreach ($usableRows as $r) {
                    if ($r['type'] === 'income')    $totalIncome    += $r['amount'];
                    if ($r['type'] === 'deduction') $totalDeduction += $r['amount'];
                }

                $slip = PayrollSlip::create([
                    'slip_number'     => PayrollSlip::generateSlipNumber($validated['period_year'], $validated['period_month']),
                    'company_id'      => $validated['company_id'],
                    'employee_id'     => $employee->id,
                    'period_month'    => $validated['period_month'],
                    'period_year'     => $validated['period_year'],
                    'cutoff_start'    => $validated['cutoff_start'] ?? null,
                    'cutoff_end'      => $validated['cutoff_end'] ?? null,
                    'payment_date'    => $validated['payment_date'] ?? null,
                    'released_at'     => $validated['released_at']  ?? null,
                    'total_income'    => $totalIncome,
                    'total_deduction' => $totalDeduction,
                    'take_home_pay'   => $totalIncome - $totalDeduction,
                    'status'          => $status,
                ]);

                $slip->applyDefaultSignatures()->save();

                foreach ($usableRows as $i => $r) {
                    PayrollItem::create([
                        'payroll_slip_id' => $slip->id,
                        'type'            => $r['type'],
                        'label'           => $r['label'],
                        'amount'          => $r['amount'],
                        'sort_order'      => $i,
                    ]);
                }

                $created++;
            }
        });

        $msg = "Berhasil membuat {$created} slip gaji.";
        if ($skippedExisting > 0) $msg .= " {$skippedExisting} dilewati (sudah ada untuk periode ini).";
        if ($skippedNoSalary > 0) $msg .= " {$skippedNoSalary} dilewati (belum memiliki Gaji Pokok / komponen).";

        return redirect()
            ->route('payroll-slips.index', [
                'company_id' => $validated['company_id'],
                'month'      => $validated['period_month'],
                'year'       => $validated['period_year'],
            ])
            ->with($created > 0 ? 'success' : 'warning', $msg);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id'   => 'required|exists:companies,id',
            'employee_id'  => 'required|exists:employees,id',
            'period_month' => 'required|integer|between:1,12',
            'period_year'  => 'required|integer|min:2000|max:2099',
            'cutoff_start' => 'nullable|date',
            'cutoff_end'   => 'nullable|date|after_or_equal:cutoff_start',
            'payment_date' => 'nullable|date',
            'notes'        => 'nullable|string|max:1000',
            'items'        => 'required|array|min:1',
            'items.*.type'   => 'required|in:income,deduction',
            'items.*.label'  => 'required|string|max:255',
            'items.*.amount' => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $request) {
            $totalIncome    = 0;
            $totalDeduction = 0;

            foreach ($validated['items'] as $item) {
                if ($item['type'] === 'income') {
                    $totalIncome += $item['amount'];
                } else {
                    $totalDeduction += $item['amount'];
                }
            }

            $slip = PayrollSlip::create([
                'slip_number'     => PayrollSlip::generateSlipNumber($validated['period_year'], $validated['period_month']),
                'company_id'      => $validated['company_id'],
                'employee_id'     => $validated['employee_id'],
                'period_month'    => $validated['period_month'],
                'period_year'     => $validated['period_year'],
                'cutoff_start'    => $validated['cutoff_start'] ?? null,
                'cutoff_end'      => $validated['cutoff_end'] ?? null,
                'payment_date'    => $validated['payment_date'] ?? null,
                'total_income'    => $totalIncome,
                'total_deduction' => $totalDeduction,
                'take_home_pay'   => $totalIncome - $totalDeduction,
                'notes'           => $validated['notes'] ?? null,
                'status'          => $request->input('action') === 'publish' ? 'published' : 'draft',
            ]);

            $slip->applyDefaultSignatures()->save();

            foreach ($validated['items'] as $index => $item) {
                PayrollItem::create([
                    'payroll_slip_id' => $slip->id,
                    'type'            => $item['type'],
                    'label'           => $item['label'],
                    'amount'          => $item['amount'],
                    'sort_order'      => $index,
                ]);
            }
        });

        return redirect()->route('payroll-slips.index')->with('success', 'Slip gaji berhasil dibuat.');
    }

    public function show(PayrollSlip $payrollSlip)
    {
        $payrollSlip->load(['company', 'employee', 'incomes', 'deductions', 'signer']);
        return view('payroll-slips.show', compact('payrollSlip'));
    }

    public function edit(PayrollSlip $payrollSlip)
    {
        $payrollSlip->load(['company', 'employee', 'items']);
        $companies = Company::orderBy('name')->get();
        $employees = Employee::where('company_id', $payrollSlip->company_id)
            ->where('is_active', true)->orderBy('name')->get();

        return view('payroll-slips.edit', compact('payrollSlip', 'companies', 'employees'));
    }

    public function update(Request $request, PayrollSlip $payrollSlip)
    {
        $validated = $request->validate([
            'company_id'         => 'required|exists:companies,id',
            'employee_id'        => 'required|exists:employees,id',
            'period_month'       => 'required|integer|between:1,12',
            'period_year'        => 'required|integer|min:2000|max:2099',
            'cutoff_start'       => 'nullable|date',
            'cutoff_end'         => 'nullable|date|after_or_equal:cutoff_start',
            'payment_date'       => 'nullable|date',
            'released_at'        => 'nullable|date',
            'signed_at'          => 'nullable|date',
            'employee_signed_at' => 'nullable|date',
            'notes'              => 'nullable|string|max:1000',
            'items'              => 'required|array|min:1',
            'items.*.type'       => 'required|in:income,deduction',
            'items.*.label'      => 'required|string|max:255',
            'items.*.amount'     => 'required|numeric|min:0',
        ]);

        DB::transaction(function () use ($validated, $request, $payrollSlip) {
            $totalIncome    = 0;
            $totalDeduction = 0;

            foreach ($validated['items'] as $item) {
                if ($item['type'] === 'income') {
                    $totalIncome += $item['amount'];
                } else {
                    $totalDeduction += $item['amount'];
                }
            }

            $payload = [
                'company_id'      => $validated['company_id'],
                'employee_id'     => $validated['employee_id'],
                'period_month'    => $validated['period_month'],
                'period_year'     => $validated['period_year'],
                'cutoff_start'    => $validated['cutoff_start'] ?? null,
                'cutoff_end'      => $validated['cutoff_end'] ?? null,
                'payment_date'    => $validated['payment_date'] ?? null,
                'total_income'    => $totalIncome,
                'total_deduction' => $totalDeduction,
                'take_home_pay'   => $totalIncome - $totalDeduction,
                'notes'           => $validated['notes'] ?? null,
                'status'          => $request->input('action') === 'publish' ? 'published' : 'draft',
            ];

            // Admin-only fields: release date and signature dates
            if (auth()->check() && auth()->user()->isAdmin()) {
                $payload['released_at'] = $validated['released_at'] ?? null;

                // Signature dates: clearing the input clears the signature
                if (array_key_exists('signed_at', $validated)) {
                    $payload['signed_at'] = $validated['signed_at'] ?? null;
                    if (empty($validated['signed_at'])) {
                        $payload['signed_by'] = null;
                    } elseif (! $payrollSlip->signed_by) {
                        $payload['signed_by'] = auth()->id();
                    }
                }
                if (array_key_exists('employee_signed_at', $validated)) {
                    $payload['employee_signed_at'] = $validated['employee_signed_at'] ?? null;
                }
            }

            $payrollSlip->update($payload);

            $payrollSlip->items()->delete();

            foreach ($validated['items'] as $index => $item) {
                PayrollItem::create([
                    'payroll_slip_id' => $payrollSlip->id,
                    'type'            => $item['type'],
                    'label'           => $item['label'],
                    'amount'          => $item['amount'],
                    'sort_order'      => $index,
                ]);
            }
        });

        return redirect()->route('payroll-slips.show', $payrollSlip)->with('success', 'Slip gaji berhasil diperbarui.');
    }

    public function destroy(PayrollSlip $payrollSlip)
    {
        $payrollSlip->delete();
        return redirect()->route('payroll-slips.index')->with('success', 'Slip gaji berhasil dihapus.');
    }

    public function downloadPdf(PayrollSlip $payrollSlip)
    {
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $payrollSlip->load(['company', 'employee', 'incomes', 'deductions', 'signer']);
        $pdf = Pdf::loadView('payroll-slips.pdf', compact('payrollSlip'))
            ->setPaper('a4', 'portrait');

        $filename = 'SlipGaji-' . $payrollSlip->slip_number . '.pdf';
        return $pdf->download($filename);
    }

    /**
     * Download multiple slips as a single PDF file (one slip per page).
     */
    public function bulkDownload(Request $request)
    {
        $validated = $request->validate([
            'slip_ids'   => 'required|array|min:1',
            'slip_ids.*' => 'integer|exists:payroll_slips,id',
        ]);

        // Generating many PDFs can be slow / memory-heavy. Be generous.
        @set_time_limit(0);
        @ini_set('memory_limit', '512M');

        $slips = PayrollSlip::with(['company', 'employee', 'incomes', 'deductions', 'signer'])
            ->whereIn('id', $validated['slip_ids'])
            ->orderBy('company_id')
            ->orderBy('employee_id')
            ->get();

        if ($slips->isEmpty()) {
            return redirect()->back()->with('warning', 'Tidak ada slip terpilih untuk diunduh.');
        }

        // Single slip → just stream the PDF directly
        if ($slips->count() === 1) {
            return $this->downloadPdf($slips->first());
        }

        try {
            $pdf = Pdf::loadView('payroll-slips.pdf-bundle', [
                    'slips' => $slips,
                    'title' => 'Slip Gaji - Bundle (' . $slips->count() . ')',
                ])
                ->setPaper('a4', 'portrait');

            $filename = 'SlipGaji-Bundle-' . date('Ymd-His') . '.pdf';
            return $pdf->download($filename);
        } catch (\Throwable $e) {
            \Log::error('bulkDownload PDF failed', ['error' => $e->getMessage()]);
            return redirect()->back()->with('error', 'Gagal membuat PDF gabungan: ' . $e->getMessage());
        }
    }

    private function safeFilename(string $name): string
    {
        $name = preg_replace('/[^A-Za-z0-9 _\-]/', '', $name);
        $name = preg_replace('/\s+/', '_', trim($name));
        return $name !== '' ? $name : 'file';
    }

    public function publish(PayrollSlip $payrollSlip)
    {
        $payrollSlip->update(['status' => 'published']);
        return redirect()->back()->with('success', 'Slip gaji berhasil dipublikasikan.');
    }

    public function sign(PayrollSlip $payrollSlip)
    {
        abort_unless(auth()->check() && auth()->user()->canSign(), 403, 'Anda tidak memiliki izin untuk menandatangani slip.');
        abort_if($payrollSlip->status !== 'published', 422, 'Slip harus berstatus Published sebelum dapat ditandatangani.');
        abort_if($payrollSlip->isSigned(), 422, 'Slip ini sudah ditandatangani.');

        $payrollSlip->update([
            'signed_by' => auth()->id(),
            'signed_at' => now(),
        ]);

        return redirect()->back()->with('success', 'Slip gaji berhasil ditandatangani.');
    }
}
