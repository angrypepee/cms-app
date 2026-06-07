<?php

namespace App\Http\Controllers;

use App\Enums\EmployeeCategory;
use App\Models\Company;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;

class EmployeeController extends Controller
{
    public function index(Request $request)
    {
        $query = Employee::with(['company', 'user']);

        if ($request->filled('search')) {
            $q = $request->search;
            $query->where(function ($builder) use ($q) {
                $builder->where('name', 'like', "%$q%")
                    ->orWhere('employee_id', 'like', "%$q%")
                    ->orWhere('position', 'like', "%$q%");
            });
        }

        if ($request->filled('company_id')) {
            $query->where('company_id', $request->company_id);
        }

        if ($request->filled('category')) {
            $query->where('employee_category', $request->category);
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->status);
        }

        $employees = $query->latest()->paginate(15)->withQueryString();
        $companies  = Company::orderBy('name')->get();
        $categories = EmployeeCategory::cases();

        $stats = [
            'total'    => Employee::count(),
            'active'   => Employee::where('is_active', true)->count(),
            'inactive' => Employee::where('is_active', false)->count(),
            'with_account' => Employee::whereNotNull('user_id')->count(),
        ];

        return view('employees.index', compact('employees', 'companies', 'categories', 'stats'));
    }

    public function create()
    {
        $companies   = Company::orderBy('name')->get();
        $categories  = EmployeeCategory::cases();
        $positions   = Position::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('employees.create', compact('companies', 'categories', 'positions', 'departments'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'company_id'             => 'required|exists:companies,id',
            'name'                   => 'required|string|max:255',
            'position'               => 'nullable|string|max:100',
            'department'             => 'nullable|string|max:100',
            'grade'                  => 'nullable|string|max:50',
            'bank_name'              => 'nullable|string|max:100',
            'bank_account'           => 'nullable|string|max:50',
            'npwp'                   => 'nullable|string|max:50',
            'bpjs_kesehatan'         => 'nullable|string|max:50',
            'bpjs_ketenagakerjaan'   => 'nullable|string|max:50',
            'employee_category'      => 'required|string|in:' . implode(',', array_column(EmployeeCategory::cases(), 'value')),
            'is_active'              => 'boolean',
            'contract_start'         => 'nullable|date',
            'contract_end'           => 'nullable|date|after_or_equal:contract_start',
            'base_salary'            => 'nullable|numeric|min:0',
            'salary_components'              => 'nullable|array',
            'salary_components.*.label'      => 'nullable|string|max:100',
            'salary_components.*.type'       => 'nullable|in:income,deduction',
            'salary_components.*.amount'     => 'nullable|numeric|min:0',
            'github_url'             => 'nullable|url|max:255',
            'gitlab_url'             => 'nullable|url|max:255',
            'linkedin_url'           => 'nullable|url|max:255',
            'portfolio_url'          => 'nullable|url|max:255',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        $validated['salary_components'] = $this->normalizeSalaryComponents($validated['salary_components'] ?? []);
        $validated['employee_id'] = Employee::generateEmployeeId();

        Employee::create($validated);
        return redirect()->route('employees.index')->with('success', 'Karyawan berhasil ditambahkan.');
    }

    public function show(Employee $employee)
    {
        $employee->load(
            'company', 'payrollSlips', 'documents.uploader', 'user',
            'contractDocuments.creator',
            'appreciationBudgets.claims',
            'reimbursements.approver',
            'projects.client',
            'portfolios.uploader'
        );
        return view('employees.show', compact('employee'));
    }

    public function edit(Employee $employee)
    {
        $employee->load('user', 'documents', 'portfolios.uploader');
        $mainContractDocument = $employee->contractDocuments()
            ->with('signer')
            ->orderByDesc('contract_date')
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->first();
        $companies   = Company::orderBy('name')->get();
        $categories  = EmployeeCategory::cases();
        $positions   = Position::where('is_active', true)->orderBy('name')->get();
        $departments = Department::where('is_active', true)->orderBy('name')->get();
        return view('employees.edit', compact('employee', 'companies', 'categories', 'positions', 'departments', 'mainContractDocument'));
    }

    public function update(Request $request, Employee $employee)
    {
        $validated = $request->validate([
            'company_id'             => 'required|exists:companies,id',
            'name'                   => 'required|string|max:255',
            'position'               => 'nullable|string|max:100',
            'department'             => 'nullable|string|max:100',
            'grade'                  => 'nullable|string|max:50',
            'bank_name'              => 'nullable|string|max:100',
            'bank_account'           => 'nullable|string|max:50',
            'npwp'                   => 'nullable|string|max:50',
            'bpjs_kesehatan'         => 'nullable|string|max:50',
            'bpjs_ketenagakerjaan'   => 'nullable|string|max:50',
            'employee_category'      => 'required|string|in:' . implode(',', array_column(EmployeeCategory::cases(), 'value')),
            'base_salary'            => 'nullable|numeric|min:0',
            'salary_components'              => 'nullable|array',
            'salary_components.*.label'      => 'nullable|string|max:100',
            'salary_components.*.type'       => 'nullable|in:income,deduction',
            'salary_components.*.amount'     => 'nullable|numeric|min:0',
            'github_url'             => 'nullable|url|max:255',
            'gitlab_url'             => 'nullable|url|max:255',
            'linkedin_url'           => 'nullable|url|max:255',
            'portfolio_url'          => 'nullable|url|max:255',
        ]);

        $validated['is_active'] = $request->boolean('is_active');
        $validated['salary_components'] = $this->normalizeSalaryComponents($validated['salary_components'] ?? []);
        $this->syncContractDatesFromMainDocument($employee, $validated);

        $employee->update($validated);
        return redirect()->route('employees.index')->with('success', 'Data karyawan berhasil diperbarui.');
    }

    public function destroy(Employee $employee)
    {
        $employee->delete();
        return redirect()->route('employees.index')->with('success', 'Karyawan berhasil dihapus.');
    }

    public function toggleActive(Employee $employee)
    {
        $employee->update(['is_active' => !$employee->is_active]);
        $label = $employee->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return redirect()->back()->with('success', "Karyawan {$employee->name} berhasil {$label}.");
    }

    public function getByCompany(Company $company)
    {
        return response()->json(
            $company->employees()->where('is_active', true)->orderBy('name')->get(['id', 'name', 'employee_id', 'position', 'department', 'grade'])
        );
    }

    /** Drop empty rows and coerce shape: [{label,type,amount}] */
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

    private function syncContractDatesFromMainDocument(Employee $employee, array &$validated): void
    {
        $mainContract = $employee->contractDocuments()
            ->orderByDesc('contract_date')
            ->orderByDesc('start_date')
            ->orderByDesc('id')
            ->first();

        if (!$mainContract) {
            return;
        }

        $validated['contract_start'] = $mainContract->start_date?->toDateString();
        $validated['contract_end'] = $mainContract->end_date?->toDateString();
    }
}
