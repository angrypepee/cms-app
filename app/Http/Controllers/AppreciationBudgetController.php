<?php

namespace App\Http\Controllers;

use App\Models\AppreciationBudget;
use App\Models\Company;
use App\Models\Employee;
use Illuminate\Http\Request;

class AppreciationBudgetController extends Controller
{
    public function index(Request $request)
    {
        $query = AppreciationBudget::with('employee', 'company');

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }
        if ($request->filled('year')) {
            $query->where('year', $request->year);
        }

        $budgets   = $query->latest()->paginate(20)->withQueryString();
        $companies = Company::orderBy('name')->get();
        $years     = range(date('Y'), date('Y') - 5);

        return view('appreciation.index', compact('budgets', 'companies', 'years'));
    }

    public function create()
    {
        $companies = Company::orderBy('name')->get();
        $employees = Employee::where('is_active', true)->orderBy('name')->with('company')->get();
        return view('appreciation.create', compact('companies', 'employees'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id'   => 'required|exists:companies,id',
            'employee_id'  => 'required|exists:employees,id',
            'year'         => 'required|integer|min:2000|max:2100',
            'total_amount' => 'required|numeric|min:0',
            'notes'        => 'nullable|string|max:1000',
        ], [
            'employee_id.unique' => 'Karyawan ini sudah memiliki anggaran apresiasi untuk tahun tersebut.',
        ]);

        // Guard duplicate per employee per year
        $exists = AppreciationBudget::where('employee_id', $validated['employee_id'])
            ->where('year', $validated['year'])->exists();
        if ($exists) {
            return back()->withInput()->withErrors([
                'employee_id' => 'Karyawan ini sudah memiliki anggaran apresiasi untuk tahun ' . $validated['year'] . '.',
            ]);
        }

        $validated['created_by'] = auth()->id();

        AppreciationBudget::create($validated);

        return redirect()->route('appreciation.index')->with('success', 'Anggaran apresiasi berhasil ditambahkan.');
    }

    public function show(AppreciationBudget $appreciation)
    {
        $appreciation->load('employee.company', 'creator', 'claims.submitter', 'claims.reviewer', 'claims.documents');
        return view('appreciation.show', compact('appreciation'));
    }

    public function destroy(AppreciationBudget $appreciation)
    {
        $appreciation->delete();
        return redirect()->route('appreciation.index')->with('success', 'Anggaran apresiasi berhasil dihapus.');
    }
}
