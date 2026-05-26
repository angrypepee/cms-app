<?php

namespace App\Http\Controllers;

use App\Models\Bonus;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Http\Request;

class BonusController extends Controller
{
    public function index(Request $request)
    {
        $query = Bonus::with('employee', 'company');

        if ($request->filled('bonus_type')) {
            $query->where('bonus_type', $request->bonus_type);
        }
        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }
        if ($request->filled('year')) {
            $query->where('period_year', $request->year);
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $bonuses   = $query->latest()->paginate(20)->withQueryString();
        $companies = Company::orderBy('name')->get();
        $years     = range(date('Y'), date('Y') - 5);

        return view('bonuses.index', compact('bonuses', 'companies', 'years'));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $employees = Employee::where('is_active', true)->orderBy('name')->with('company')->get();
        return view('bonuses.create', compact('companies', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id'   => 'required|exists:companies,id',
            'employee_id'  => 'required|exists:employees,id',
            'bonus_type'   => 'required|in:thr,project',
            'title'        => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'period_year'  => 'required|integer|min:2000|max:2100',
            'period_month' => 'nullable|integer|min:1|max:12',
            'payment_date' => 'nullable|date',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $validated['created_by'] = auth()->id();
        $validated['status']     = 'draft';

        Bonus::create($validated);

        return redirect()->route('bonuses.index')->with('success', 'Bonus berhasil dibuat.');
    }

    public function show(Bonus $bonus)
    {
        $bonus->load('employee.company', 'creator');
        return view('bonuses.show', compact('bonus'));
    }

    public function edit(Bonus $bonus)
    {
        $companies = Company::orderBy('name')->get();
        $employees = Employee::where('is_active', true)->orderBy('name')->with('company')->get();
        return view('bonuses.edit', compact('bonus', 'companies', 'employees'));
    }

    public function update(Request $request, Bonus $bonus)
    {
        $validated = $request->validate([
            'company_id'   => 'required|exists:companies,id',
            'employee_id'  => 'required|exists:employees,id',
            'bonus_type'   => 'required|in:thr,project',
            'title'        => 'required|string|max:255',
            'amount'       => 'required|numeric|min:0',
            'period_year'  => 'required|integer|min:2000|max:2100',
            'period_month' => 'nullable|integer|min:1|max:12',
            'payment_date' => 'nullable|date',
            'notes'        => 'nullable|string|max:1000',
        ]);

        $bonus->update($validated);

        return redirect()->route('bonuses.show', $bonus)->with('success', 'Bonus berhasil diperbarui.');
    }

    public function markPaid(Bonus $bonus)
    {
        abort_if($bonus->isPaid(), 422, 'Sudah dibayar.');
        $bonus->update(['status' => 'paid', 'payment_date' => $bonus->payment_date ?? now()->toDateString()]);
        return back()->with('success', 'Bonus ditandai sebagai sudah dibayar.');
    }

    public function destroy(Bonus $bonus)
    {
        $bonus->delete();
        return redirect()->route('bonuses.index')->with('success', 'Bonus berhasil dihapus.');
    }
}
