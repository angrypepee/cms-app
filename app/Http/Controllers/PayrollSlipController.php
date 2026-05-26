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
                'slip_number'     => PayrollSlip::generateSlipNumber(),
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

            $payrollSlip->update([
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
        $payrollSlip->load(['company', 'employee', 'incomes', 'deductions']);
        $pdf = Pdf::loadView('payroll-slips.pdf', compact('payrollSlip'))
            ->setPaper('a4', 'portrait');

        $filename = 'SlipGaji-' . $payrollSlip->slip_number . '.pdf';
        return $pdf->download($filename);
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
