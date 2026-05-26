<?php

namespace App\Http\Controllers;

use App\Enums\EmployeeCategory;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Position;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MasterDataController extends Controller
{
    public function index()
    {
        $positions   = Position::withCount('employees')->orderBy('name')->get();
        $departments = Department::withCount('employees')->orderBy('name')->get();

        // Counts per employee category (enum, system-managed)
        $categoryCounts = Employee::select('employee_category', DB::raw('COUNT(*) as total'))
            ->groupBy('employee_category')
            ->pluck('total', 'employee_category');
        $categories = EmployeeCategory::cases();

        return view('master-data.index', compact('positions', 'departments', 'categories', 'categoryCounts'));
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
}
