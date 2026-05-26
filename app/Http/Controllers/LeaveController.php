<?php

namespace App\Http\Controllers;

use App\Models\Employee;
use App\Models\LeaveRequest;
use App\Models\LeaveType;
use App\Models\User;
use App\Notifications\LeaveStatusChangedNotification;
use Illuminate\Http\Request;

class LeaveController extends Controller
{
    private function authorizeStaff(): void
    {
        abort_unless(auth()->check() && auth()->user()->isStaff(), 403);
    }

    public function index(Request $request)
    {
        $this->authorizeStaff();

        $query = LeaveRequest::with(['employee.company', 'leaveType', 'reviewer'])
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('leave_type_id')) {
            $query->where('leave_type_id', $request->leave_type_id);
        }
        if ($request->filled('search')) {
            $query->whereHas('employee', fn($q) =>
                $q->where('name', 'like', '%' . $request->search . '%')
            );
        }

        $leaveRequests = $query->paginate(20)->withQueryString();
        $leaveTypes    = LeaveType::orderBy('name')->get();

        return view('leaves.index', compact('leaveRequests', 'leaveTypes'));
    }

    public function show(LeaveRequest $leaveRequest)
    {
        $this->authorizeStaff();
        $leaveRequest->load(['employee.company', 'leaveType', 'reviewer']);
        return view('leaves.show', compact('leaveRequest'));
    }

    public function approve(Request $request, LeaveRequest $leaveRequest)
    {
        $this->authorizeStaff();
        abort_if($leaveRequest->status !== 'pending', 422, 'Permohonan sudah diproses.');

        $request->validate(['admin_notes' => 'nullable|string|max:500']);

        $leaveRequest->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        // Notify employee
        $leaveRequest->load(['leaveType', 'employee.user']);
        optional($leaveRequest->employee->user)->notify(new LeaveStatusChangedNotification($leaveRequest));

        return redirect()->route('leaves.show', $leaveRequest)
            ->with('success', 'Permohonan cuti disetujui.');
    }

    public function reject(Request $request, LeaveRequest $leaveRequest)
    {
        $this->authorizeStaff();
        abort_if($leaveRequest->status !== 'pending', 422, 'Permohonan sudah diproses.');

        $request->validate(['admin_notes' => 'nullable|string|max:500']);

        $leaveRequest->update([
            'status'      => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        // Notify employee
        $leaveRequest->load(['leaveType', 'employee.user']);
        optional($leaveRequest->employee->user)->notify(new LeaveStatusChangedNotification($leaveRequest));

        return redirect()->route('leaves.show', $leaveRequest)
            ->with('success', 'Permohonan cuti ditolak.');
    }

    // ── Leave Type Management ─────────────────────────────────────────────────

    public function leaveTypes()
    {
        $this->authorizeStaff();
        $leaveTypes = LeaveType::withCount('leaveRequests')->get();
        return view('leaves.types', compact('leaveTypes'));
    }

    public function storeLeaveType(Request $request)
    {
        $this->authorizeStaff();
        $validated = $request->validate([
            'name'              => 'required|string|max:100|unique:leave_types,name',
            'max_days_per_year' => 'required|integer|min:0',
            'is_paid'           => 'boolean',
            'color'             => 'nullable|string|max:20',
        ]);
        $validated['is_paid']  = $request->boolean('is_paid', true);
        $validated['color']    = $validated['color'] ?? '#2563eb';
        LeaveType::create($validated);
        return redirect()->route('leaves.types')->with('success', 'Jenis cuti berhasil ditambahkan.');
    }

    public function updateLeaveType(Request $request, LeaveType $leaveType)
    {
        $this->authorizeStaff();
        $validated = $request->validate([
            'name'              => 'required|string|max:100|unique:leave_types,name,' . $leaveType->id,
            'max_days_per_year' => 'required|integer|min:0',
            'is_paid'           => 'boolean',
            'color'             => 'nullable|string|max:20',
            'is_active'         => 'boolean',
        ]);
        $validated['is_paid']   = $request->boolean('is_paid', true);
        $validated['is_active'] = $request->boolean('is_active', true);
        $leaveType->update($validated);
        return redirect()->route('leaves.types')->with('success', 'Jenis cuti berhasil diperbarui.');
    }

    public function destroyLeaveType(LeaveType $leaveType)
    {
        $this->authorizeStaff();
        abort_if($leaveType->leaveRequests()->exists(), 422, 'Jenis cuti sudah digunakan.');
        $leaveType->delete();
        return redirect()->route('leaves.types')->with('success', 'Jenis cuti dihapus.');
    }
}
