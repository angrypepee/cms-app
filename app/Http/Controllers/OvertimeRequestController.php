<?php

namespace App\Http\Controllers;

use App\Models\OvertimeRequest;
use App\Models\Employee;
use Illuminate\Http\Request;

class OvertimeRequestController extends Controller
{
    private function authorizeStaff(): void
    {
        abort_unless(auth()->check() && auth()->user()->isStaff(), 403);
    }

    // ── Admin / HR ─────────────────────────────────────────────

    public function index(Request $request)
    {
        $this->authorizeStaff();

        $query = OvertimeRequest::with(['employee.company', 'reviewer'])
            ->orderByDesc('date')
            ->orderByDesc('created_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }
        if ($request->filled('search')) {
            $query->whereHas('employee', fn($q) =>
                $q->where('name', 'like', '%' . $request->search . '%')
            );
        }

        $overtimeRequests = $query->paginate(20)->withQueryString();

        return view('overtime.index', compact('overtimeRequests'));
    }

    public function show(OvertimeRequest $overtimeRequest)
    {
        $this->authorizeStaff();
        $overtimeRequest->load(['employee.company', 'reviewer']);
        return view('overtime.show', compact('overtimeRequest'));
    }

    public function approve(Request $request, OvertimeRequest $overtimeRequest)
    {
        $this->authorizeStaff();
        abort_if($overtimeRequest->status !== 'pending', 422, 'Permohonan sudah diproses.');

        $request->validate(['admin_notes' => 'nullable|string|max:500']);

        $overtimeRequest->update([
            'status'      => 'approved',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('overtime.show', $overtimeRequest)
            ->with('success', 'Pengajuan lembur disetujui.');
    }

    public function reject(Request $request, OvertimeRequest $overtimeRequest)
    {
        $this->authorizeStaff();
        abort_if($overtimeRequest->status !== 'pending', 422, 'Permohonan sudah diproses.');

        $request->validate(['admin_notes' => 'nullable|string|max:500']);

        $overtimeRequest->update([
            'status'      => 'rejected',
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
            'admin_notes' => $request->admin_notes,
        ]);

        return redirect()->route('overtime.show', $overtimeRequest)
            ->with('success', 'Pengajuan lembur ditolak.');
    }

    // ── Employee Portal ────────────────────────────────────────

    public function myIndex(Request $request)
    {
        $employee = auth()->user()->employee;
        abort_unless($employee, 403);

        $overtimeRequests = OvertimeRequest::where('employee_id', $employee->id)
            ->orderByDesc('date')
            ->paginate(15);

        return view('employee-portal.overtime', compact('overtimeRequests'));
    }

    public function myStore(Request $request)
    {
        $employee = auth()->user()->employee;
        abort_unless($employee, 403);

        $validated = $request->validate([
            'date'       => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time'   => 'required|date_format:H:i|after:start_time',
            'reason'     => 'required|string|max:1000',
        ]);

        // Prevent duplicate on same date
        $exists = OvertimeRequest::where('employee_id', $employee->id)
            ->where('date', $validated['date'])
            ->whereIn('status', ['pending', 'approved'])
            ->exists();

        if ($exists) {
            return back()->withErrors(['date' => 'Sudah ada pengajuan lembur pada tanggal tersebut.'])->withInput();
        }

        OvertimeRequest::create(array_merge($validated, ['employee_id' => $employee->id]));

        return redirect()->route('my.overtime')->with('success', 'Pengajuan lembur berhasil dikirim.');
    }
}
