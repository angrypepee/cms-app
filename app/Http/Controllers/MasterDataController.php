<?php

namespace App\Http\Controllers;

use App\Enums\EmployeeCategory;
use App\Models\Department;
use App\Models\Employee;
use App\Models\FirstParty;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterDataController extends Controller
{
    public function index()
    {
        $positions   = Position::withCount('employees')->orderBy('name')->get();
        $departments = Department::withCount('employees')->orderBy('name')->get();
        $firstParties = FirstParty::orderByDesc('is_active')->orderBy('name')->get();

        // Counts per employee category (enum, system-managed)
        $categoryCounts = Employee::select('employee_category', DB::raw('COUNT(*) as total'))
            ->groupBy('employee_category')
            ->pluck('total', 'employee_category');
        $categories = EmployeeCategory::cases();

        return view('master-data.index', compact('positions', 'departments', 'categories', 'categoryCounts', 'firstParties'));
    }

    // ── Positions ────────────────────────────────────────────────
    public function storePosition(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:150|unique:positions,name',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        Position::create($validated);
        return redirect()->route('master-data.index', ['tab' => 'positions'])
            ->with('success', "Jabatan \"{$validated['name']}\" berhasil ditambahkan.");
    }

    public function updatePosition(Request $request, Position $position)
    {
        $validated = $request->validate([
            'name'        => "required|string|max:150|unique:positions,name,{$position->id}",
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', false);

        // Rename on employees if name changed
        if ($validated['name'] !== $position->name) {
            Employee::where('position', $position->name)->update(['position' => $validated['name']]);
        }

        $position->update($validated);
        return redirect()->route('master-data.index', ['tab' => 'positions'])
            ->with('success', "Jabatan berhasil diperbarui.");
    }

    public function destroyPosition(Position $position)
    {
        $inUse = Employee::where('position', $position->name)->count();
        if ($inUse > 0) {
            return back()->with('warning', "Jabatan \"{$position->name}\" sedang digunakan oleh {$inUse} karyawan. Nonaktifkan terlebih dahulu atau ubah jabatan karyawan tersebut.");
        }
        $name = $position->name;
        $position->delete();
        return redirect()->route('master-data.index', ['tab' => 'positions'])
            ->with('success', "Jabatan \"{$name}\" berhasil dihapus.");
    }

    // ── Departments ──────────────────────────────────────────────
    public function storeDepartment(Request $request)
    {
        $validated = $request->validate([
            'name'        => 'required|string|max:150|unique:departments,name',
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', true);
        Department::create($validated);
        return redirect()->route('master-data.index', ['tab' => 'departments'])
            ->with('success', "Departemen \"{$validated['name']}\" berhasil ditambahkan.");
    }

    public function updateDepartment(Request $request, Department $department)
    {
        $validated = $request->validate([
            'name'        => "required|string|max:150|unique:departments,name,{$department->id}",
            'description' => 'nullable|string|max:500',
            'is_active'   => 'boolean',
        ]);
        $validated['is_active'] = $request->boolean('is_active', false);

        if ($validated['name'] !== $department->name) {
            Employee::where('department', $department->name)->update(['department' => $validated['name']]);
        }

        $department->update($validated);
        return redirect()->route('master-data.index', ['tab' => 'departments'])
            ->with('success', "Departemen berhasil diperbarui.");
    }

    public function destroyDepartment(Department $department)
    {
        $inUse = Employee::where('department', $department->name)->count();
        if ($inUse > 0) {
            return back()->with('warning', "Departemen \"{$department->name}\" sedang digunakan oleh {$inUse} karyawan. Nonaktifkan terlebih dahulu atau pindahkan karyawan tersebut.");
        }
        $name = $department->name;
        $department->delete();
        return redirect()->route('master-data.index', ['tab' => 'departments'])
            ->with('success', "Departemen \"{$name}\" berhasil dihapus.");
    }

    // ── First Parties ─────────────────────────────────────────────
    public function storeFirstParty(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:first_parties,name',
            'representative_name' => 'nullable|string|max:255',
            'representative_position' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', true);
        FirstParty::create($validated);

        return redirect()->route('master-data.index', ['tab' => 'first-parties'])
            ->with('success', "Pihak pertama \"{$validated['name']}\" berhasil ditambahkan.");
    }

    public function updateFirstParty(Request $request, FirstParty $firstParty)
    {
        $validated = $request->validate([
            'name' => "required|string|max:255|unique:first_parties,name,{$firstParty->id}",
            'representative_name' => 'nullable|string|max:255',
            'representative_position' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'is_active' => 'boolean',
        ]);

        $validated['is_active'] = $request->boolean('is_active', false);
        $firstParty->update($validated);

        return redirect()->route('master-data.index', ['tab' => 'first-parties'])
            ->with('success', 'Data pihak pertama berhasil diperbarui.');
    }

    public function destroyFirstParty(FirstParty $firstParty)
    {
        $name = $firstParty->name;
        $firstParty->delete();

        return redirect()->route('master-data.index', ['tab' => 'first-parties'])
            ->with('success', "Pihak pertama \"{$name}\" berhasil dihapus.");
    }
}
