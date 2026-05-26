<?php

namespace App\Http\Controllers;

use App\Models\AppreciationBudget;
use App\Models\AppreciationClaim;
use App\Models\PayrollSlip;
use App\Models\Reimbursement;
use App\Models\ReimbursementDocument;
use App\Models\User;
use App\Notifications\NewLeaveRequestNotification;
use App\Notifications\NewInternalRequestNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EmployeePortalController extends Controller
{
    private function authorizeEmployee(): void
    {
        abort_unless(auth()->check() && auth()->user()->isEmployee(), 403);
        abort_unless(auth()->user()->employee !== null, 403, 'Akun Anda belum terhubung ke data karyawan. Hubungi Administrator.');
    }

    private function getEmployee()
    {
        return auth()->user()->employee;
    }

    public function dashboard(Request $request)
    {
        $this->authorizeEmployee();

        $employee = $this->getEmployee()->load('company');

        // ── Year filter ───────────────────────────────────────────────────
        $currentYear = (int) now()->year;
        $year        = (int) $request->get('year', $currentYear);

        // Build list of years that actually contain data for this employee
        $slipYears  = PayrollSlip::where('employee_id', $employee->id)->distinct()->pluck('period_year');
        $budgetYrs  = AppreciationBudget::where('employee_id', $employee->id)->distinct()->pluck('year');
        $reimbYrs   = Reimbursement::where('employee_id', $employee->id)
            ->selectRaw('DISTINCT YEAR(created_at) as y')->pluck('y');
        $leaveYrs   = \App\Models\LeaveRequest::where('employee_id', $employee->id)
            ->selectRaw('DISTINCT YEAR(start_date) as y')->pluck('y');

        $availableYears = collect([$currentYear, $year])
            ->merge($slipYears)->merge($budgetYrs)->merge($reimbYrs)->merge($leaveYrs)
            ->filter()->map(fn($y) => (int) $y)->unique()->sortDesc()->values();

        // ── Filtered data ─────────────────────────────────────────────────
        $employee->setRelation('payrollSlips',
            $employee->payrollSlips()->where('period_year', $year)->orderByDesc('created_at')->get()
        );

        $budgets = AppreciationBudget::where('employee_id', $employee->id)
            ->where('year', $year)
            ->orderByDesc('year')
            ->with(['claims' => fn($q) => $q->orderByDesc('created_at')])
            ->get();

        $reimbursements = Reimbursement::where('employee_id', $employee->id)
            ->whereYear('created_at', $year)
            ->with('approver')
            ->orderByDesc('created_at')
            ->get();

        $leaveRequests = \App\Models\LeaveRequest::where('employee_id', $employee->id)
            ->whereYear('start_date', $year)
            ->with('leaveType')
            ->orderByDesc('created_at')
            ->take(15)
            ->get();

        // Pending counters always reflect current state (not year-scoped)
        $pendingLeavesCount   = \App\Models\LeaveRequest::where('employee_id', $employee->id)->where('status', 'pending')->count();
        $pendingRequestsCount = \App\Models\InternalRequest::where('employee_id', $employee->id)->where('status', 'pending')->count();

        $approvedLeaveDays = \App\Models\LeaveRequest::where('employee_id', $employee->id)
            ->where('status', 'approved')
            ->whereYear('start_date', $year)
            ->sum('days_count');

        // Totals (scoped to selected year)
        $totalGaji          = $employee->payrollSlips->where('status', 'published')->sum('take_home_pay');
        $totalApresiasi     = $budgets->flatMap(fn($b) => $b->claims)->where('status', 'approved')->sum('amount');
        $totalReimbursement = $reimbursements->where('status', 'approved')->sum('amount');
        $grandTotal         = $totalGaji + $totalApresiasi + $totalReimbursement;

        return view('employee-portal.dashboard', compact(
            'employee', 'budgets', 'reimbursements',
            'totalGaji', 'totalApresiasi', 'totalReimbursement', 'grandTotal',
            'leaveRequests', 'pendingLeavesCount', 'approvedLeaveDays', 'pendingRequestsCount',
            'year', 'availableYears', 'currentYear'
        ));
    }

    public function slips()
    {
        $this->authorizeEmployee();

        $employee = $this->getEmployee();
        $slips    = $employee->payrollSlips()->orderByDesc('created_at')->paginate(15);

        return view('employee-portal.slips', compact('employee', 'slips'));
    }

    public function showSlip(PayrollSlip $payrollSlip)
    {
        $this->authorizeEmployee();

        $employee = $this->getEmployee();
        abort_if($payrollSlip->employee_id !== $employee->id, 403);
        abort_if($payrollSlip->status !== 'published', 403, 'Slip belum diterbitkan.');

        $payrollSlip->load(['company', 'employee', 'incomes', 'deductions', 'signer']);

        return view('payroll-slips.show', compact('payrollSlip'));
    }

    public function signSlip(PayrollSlip $payrollSlip)
    {
        $this->authorizeEmployee();

        $employee = $this->getEmployee();
        abort_if($payrollSlip->employee_id !== $employee->id, 403);
        abort_if($payrollSlip->status !== 'published', 403, 'Slip belum diterbitkan.');

        if ($payrollSlip->isEmployeeSigned()) {
            return back()->with('warning', 'Slip ini sudah Anda tandatangani sebelumnya.');
        }

        $payrollSlip->update(['employee_signed_at' => now()]);

        return back()->with('success', 'Slip gaji berhasil Anda tandatangani sebagai tanda terima.');
    }

    // ── Appreciation ─────────────────────────────────────────────────────────

    public function appreciation()
    {
        $this->authorizeEmployee();

        $employee = $this->getEmployee()->load('company');
        $budgets  = AppreciationBudget::where('employee_id', $employee->id)
            ->orderByDesc('year')
            ->with(['claims' => fn($q) => $q->orderByDesc('created_at')])
            ->get();

        return view('employee-portal.appreciation', compact('employee', 'budgets'));
    }

    public function storeClaim(Request $request, AppreciationBudget $budget)
    {
        $this->authorizeEmployee();

        $employee = $this->getEmployee();
        abort_if($budget->employee_id !== $employee->id, 403);

        $validated = $request->validate([
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string|max:2000',
            'amount'       => 'required|numeric|min:1',
            'documents'    => 'nullable|array',
            'documents.*'  => 'file|max:10240|mimes:pdf,jpg,jpeg,png,webp,doc,docx',
            'doc_labels'   => 'nullable|array',
            'doc_labels.*' => 'nullable|string|max:255',
        ]);

        $remaining = $budget->remainingAmount();
        if ($validated['amount'] > $remaining) {
            return back()->withInput()->withErrors([
                'amount' => 'Jumlah melebihi sisa anggaran (Rp ' . number_format($remaining, 0, ',', '.') . ').',
            ]);
        }

        $claim = $budget->claims()->create([
            'submitted_by' => auth()->id(),
            'title'        => $validated['title'],
            'description'  => $validated['description'] ?? null,
            'amount'       => $validated['amount'],
            'status'       => 'pending',
        ]);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $idx => $file) {
                $label = $request->input("doc_labels.$idx") ?: $file->getClientOriginalName();
                $path  = $file->store('appreciation-claims/' . $claim->id, 'private');

                $claim->documents()->create([
                    'uploaded_by'   => auth()->id(),
                    'label'         => $label,
                    'file_path'     => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type'     => $file->getMimeType(),
                    'file_size'     => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('my.appreciation')
            ->with('success', 'Permohonan "' . $claim->title . '" berhasil diajukan.');
    }

    public function showClaim(AppreciationBudget $budget, AppreciationClaim $claim)
    {
        $this->authorizeEmployee();

        $employee = $this->getEmployee();
        abort_if($budget->employee_id !== $employee->id, 403);
        abort_if($claim->appreciation_budget_id !== $budget->id, 404);

        $claim->load('submitter', 'reviewer', 'documents');

        return view('employee-portal.claim-show', compact('budget', 'claim'));
    }

    public function showClaimTransferProof(Request $request, AppreciationBudget $budget, AppreciationClaim $claim)
    {
        $this->authorizeEmployee();

        $employee = $this->getEmployee();
        abort_if($budget->employee_id !== $employee->id, 403);
        abort_if($claim->appreciation_budget_id !== $budget->id, 404);
        abort_unless($claim->hasTransferProof(), 404);

        $absolutePath = \Illuminate\Support\Facades\Storage::disk('private')->path($claim->transfer_proof_path);
        abort_unless(file_exists($absolutePath), 404);

        $ext     = pathinfo($claim->transfer_proof_path, PATHINFO_EXTENSION);
        $mimeMap = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
                    'png' => 'image/png', 'webp' => 'image/webp'];
        $mime    = $mimeMap[strtolower($ext)] ?? 'application/octet-stream';

        if ($request->boolean('download')) {
            return response()->download($absolutePath, 'bukti-transfer-' . $claim->id . '.' . $ext);
        }
        return response()->file($absolutePath, ['Content-Type' => $mime]);
    }

    public function showClaimDocument(Request $request, AppreciationBudget $budget, AppreciationClaim $claim, \App\Models\AppreciationClaimDocument $document)
    {
        $this->authorizeEmployee();

        $employee = $this->getEmployee();
        abort_if($budget->employee_id !== $employee->id, 403);
        abort_if($claim->appreciation_budget_id !== $budget->id, 404);
        abort_if($document->appreciation_claim_id !== $claim->id, 404);

        $absolutePath = \Illuminate\Support\Facades\Storage::disk('private')->path($document->file_path);
        abort_unless(file_exists($absolutePath), 404);

        $headers = ['Content-Type' => $document->mime_type ?? 'application/octet-stream'];

        if ($request->boolean('download') || !$document->isViewable()) {
            return response()->download($absolutePath, $document->original_name, $headers);
        }
        return response()->file($absolutePath, $headers);
    }

    public function destroyClaim(AppreciationBudget $budget, AppreciationClaim $claim)
    {
        $this->authorizeEmployee();

        $employee = $this->getEmployee();
        abort_if($budget->employee_id !== $employee->id, 403);
        abort_if($claim->appreciation_budget_id !== $budget->id, 404);
        abort_unless($claim->isPending(), 422, 'Hanya permohonan yang masih menunggu yang dapat dibatalkan.');

        $claim->documents()->each(function ($doc) {
            \Illuminate\Support\Facades\Storage::disk('private')->delete($doc->file_path);
            $doc->delete();
        });
        $claim->delete();

        return redirect()->route('my.appreciation')
            ->with('success', 'Permohonan berhasil dibatalkan.');
    }

    // ── Reimbursement ────────────────────────────────────────────────────────

    public function reimbursements()
    {
        $this->authorizeEmployee();

        $employee      = $this->getEmployee()->load('company');
        $reimbursements = Reimbursement::where('employee_id', $employee->id)
            ->with('approver')
            ->orderByDesc('created_at')
            ->paginate(15);

        // Non-employee users as possible approvers
        $approvers = User::whereHas('employee', function ($q) {
                // users that ARE linked to an employee with non-employee role are excluded
            }, 'is not null')
            ->where(function ($q) {
                $q->whereDoesntHave('employee')
                  ->orWhereHas('employee');
            });
        // Simpler: all users that are not Employee role
        $approvers = User::where('role', '!=', \App\Enums\UserRole::Employee->value)
            ->orderBy('name')->get();

        return view('employee-portal.reimbursements.index', compact('employee', 'reimbursements', 'approvers'));
    }

    public function storeReimbursement(Request $request)
    {
        $this->authorizeEmployee();

        $employee = $this->getEmployee();

        $validated = $request->validate([
            'approver_id'  => 'required|exists:users,id',
            'title'        => 'required|string|max:255',
            'description'  => 'nullable|string|max:2000',
            'category'     => 'nullable|string|max:100',
            'amount'       => 'required|numeric|min:1',
            'documents'    => 'nullable|array',
            'documents.*'  => 'file|max:10240|mimes:pdf,jpg,jpeg,png,webp,doc,docx',
            'doc_labels'   => 'nullable|array',
            'doc_labels.*' => 'nullable|string|max:255',
        ]);

        // Ensure approver is not an employee
        $approver = User::findOrFail($validated['approver_id']);
        abort_if($approver->isEmployee(), 422, 'Approver harus non-karyawan.');

        $reimbursement = Reimbursement::create([
            'employee_id'  => $employee->id,
            'submitted_by' => auth()->id(),
            'approver_id'  => $validated['approver_id'],
            'title'        => $validated['title'],
            'description'  => $validated['description'] ?? null,
            'category'     => $validated['category'] ?? null,
            'amount'       => $validated['amount'],
            'status'       => 'pending',
        ]);

        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $idx => $file) {
                $label = $request->input("doc_labels.$idx") ?: $file->getClientOriginalName();
                $path  = $file->store('reimbursements/' . $reimbursement->id, 'private');

                $reimbursement->documents()->create([
                    'uploaded_by'   => auth()->id(),
                    'label'         => $label,
                    'file_path'     => $path,
                    'original_name' => $file->getClientOriginalName(),
                    'mime_type'     => $file->getMimeType(),
                    'file_size'     => $file->getSize(),
                ]);
            }
        }

        return redirect()->route('my.reimbursements')
            ->with('success', 'Permohonan reimbursement "' . $reimbursement->title . '" berhasil diajukan.');
    }

    public function showReimbursement(Reimbursement $reimbursement)
    {
        $this->authorizeEmployee();

        $employee = $this->getEmployee();
        abort_if($reimbursement->employee_id !== $employee->id, 403);

        $reimbursement->load('submitter', 'approver', 'reviewer', 'documents.uploader');

        return view('employee-portal.reimbursements.show', compact('reimbursement'));
    }

    public function destroyReimbursement(Reimbursement $reimbursement)
    {
        $this->authorizeEmployee();

        $employee = $this->getEmployee();
        abort_if($reimbursement->employee_id !== $employee->id, 403);
        abort_unless($reimbursement->isPending(), 422, 'Hanya permohonan yang masih menunggu yang dapat dibatalkan.');

        $reimbursement->documents()->each(function ($doc) {
            Storage::disk('private')->delete($doc->file_path);
            $doc->delete();
        });
        $reimbursement->delete();

        return redirect()->route('my.reimbursements')
            ->with('success', 'Permohonan reimbursement berhasil dibatalkan.');
    }

    public function showReimbursementDocument(Request $request, Reimbursement $reimbursement, ReimbursementDocument $document)
    {
        $this->authorizeEmployee();

        $employee = $this->getEmployee();
        abort_if($reimbursement->employee_id !== $employee->id, 403);
        abort_if($document->reimbursement_id !== $reimbursement->id, 404);

        $absolutePath = Storage::disk('private')->path($document->file_path);
        abort_unless(file_exists($absolutePath), 404);

        $headers = ['Content-Type' => $document->mime_type ?? 'application/octet-stream'];

        if ($request->boolean('download') || !$document->isViewable()) {
            return response()->download($absolutePath, $document->original_name, $headers);
        }
        return response()->file($absolutePath, $headers);
    }

    public function showReimbursementTransferProof(Request $request, Reimbursement $reimbursement)
    {
        $this->authorizeEmployee();

        $employee = $this->getEmployee();
        abort_if($reimbursement->employee_id !== $employee->id, 403);
        abort_unless($reimbursement->hasTransferProof(), 404);

        $absolutePath = Storage::disk('private')->path($reimbursement->transfer_proof_path);
        abort_unless(file_exists($absolutePath), 404);

        $ext     = pathinfo($reimbursement->transfer_proof_path, PATHINFO_EXTENSION);
        $mimeMap = ['pdf' => 'application/pdf', 'jpg' => 'image/jpeg', 'jpeg' => 'image/jpeg',
                    'png' => 'image/png', 'webp' => 'image/webp'];
        $mime    = $mimeMap[strtolower($ext)] ?? 'application/octet-stream';

        if ($request->boolean('download')) {
            return response()->download($absolutePath, 'bukti-transfer-reimb-' . $reimbursement->id . '.' . $ext);
        }
        return response()->file($absolutePath, ['Content-Type' => $mime]);
    }

    // ── Calendar ─────────────────────────────────────────────────────────────

    public function calendar()
    {
        $this->authorizeEmployee();
        $employee = $this->getEmployee()->load('company');

        $year  = request()->get('year', now()->year);
        $month = request()->get('month', now()->month);

        // National holidays + company-specific holidays
        $holidays = \App\Models\Holiday::active()
            ->where(function ($q) use ($employee) {
                $q->where('type', 'national')
                  ->orWhere('company_id', $employee->company_id);
            })
            ->whereYear('date', $year)
            ->get();

        // Employee's own approved leave requests
        $leaveRequests = \App\Models\LeaveRequest::where('employee_id', $employee->id)
            ->with('leaveType')
            ->whereYear('start_date', $year)
            ->get();

        // Build FullCalendar events
        $events = collect();
        foreach ($holidays as $h) {
            $events->push([
                'title'      => $h->name,
                'start'      => $h->date->toDateString(),
                'color'      => $h->type === 'national' ? '#dc2626' : '#7c3aed',
                'allDay'     => true,
                'extendedProps' => ['kind' => 'holiday'],
            ]);
        }
        foreach ($leaveRequests as $lr) {
            $endDisplay = $lr->end_date->copy()->addDay()->toDateString();
            $color = match($lr->status) {
                'approved' => '#15803d',
                'rejected' => '#9ca3af',
                default    => '#d97706',
            };
            $events->push([
                'title' => $lr->leaveType->name . ' (' . $lr->statusLabel() . ')',
                'start' => $lr->start_date->toDateString(),
                'end'   => $endDisplay,
                'color' => $color,
                'url'   => route('my.leaves.show', $lr),
                'extendedProps' => ['kind' => 'leave'],
            ]);
        }

        return view('employee-portal.calendar', compact('employee', 'events', 'year', 'month'));
    }

    // ── Leave Requests ────────────────────────────────────────────────────────

    public function leaves()
    {
        $this->authorizeEmployee();
        $employee   = $this->getEmployee()->load('company');
        $leaveTypes = \App\Models\LeaveType::where('is_active', true)->orderBy('name')->get();
        $year       = request()->get('year', now()->year);

        $leaves = \App\Models\LeaveRequest::where('employee_id', $employee->id)
            ->with('leaveType', 'reviewer')
            ->whereYear('start_date', $year)
            ->orderByDesc('created_at')
            ->get();

        // Usage per leave type this year
        $usageByType = $leaves->where('status', 'approved')
            ->groupBy('leave_type_id')
            ->map(fn($g) => $g->sum('days_count'));

        // Holidays for current + next year (for the date picker disable list)
        $holidays = \App\Models\Holiday::where('is_active', true)
            ->where(function ($q) use ($year) {
                $q->whereYear('date', $year)->orWhereYear('date', $year + 1);
            })
            ->where(function ($q) use ($employee) {
                $q->where('type', 'national')
                  ->orWhere('company_id', $employee->company_id);
            })
            ->pluck('date')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->toDateString())
            ->values();

        return view('employee-portal.leaves.index', compact('employee', 'leaveTypes', 'leaves', 'year', 'usageByType', 'holidays'));
    }

    public function storeLeave(Request $request)
    {
        $this->authorizeEmployee();
        $employee = $this->getEmployee()->load('company');

        $validated = $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date'    => 'required|date|after_or_equal:today',
            'end_date'      => 'required|date|after_or_equal:start_date',
            'reason'        => 'required|string|min:10|max:1000',
        ], [
            'start_date.after_or_equal' => 'Tanggal mulai tidak boleh di masa lalu.',
            'end_date.after_or_equal'   => 'Tanggal akhir harus sama atau setelah tanggal mulai.',
            'reason.min'                => 'Alasan minimal 10 karakter.',
        ]);

        $start = \Carbon\Carbon::parse($validated['start_date']);
        $end   = \Carbon\Carbon::parse($validated['end_date']);

        // Validate start/end are not on weekend
        if ($start->isWeekend()) {
            return back()->withErrors(['start_date' => 'Tanggal mulai tidak boleh hari Sabtu atau Minggu.'])->withInput();
        }
        if ($end->isWeekend()) {
            return back()->withErrors(['end_date' => 'Tanggal akhir tidak boleh hari Sabtu atau Minggu.'])->withInput();
        }

        // Load applicable holidays
        $holidayDates = \App\Models\Holiday::where('is_active', true)
            ->whereBetween('date', [$start->toDateString(), $end->toDateString()])
            ->where(function ($q) use ($employee) {
                $q->where('type', 'national')
                  ->orWhere('company_id', $employee->company_id);
            })
            ->pluck('date')
            ->map(fn($d) => \Carbon\Carbon::parse($d)->toDateString())
            ->all();

        // Validate start/end are not on a holiday
        if (in_array($start->toDateString(), $holidayDates)) {
            return back()->withErrors(['start_date' => 'Tanggal mulai adalah hari libur nasional/perusahaan.'])->withInput();
        }
        if (in_array($end->toDateString(), $holidayDates)) {
            return back()->withErrors(['end_date' => 'Tanggal akhir adalah hari libur nasional/perusahaan.'])->withInput();
        }

        $days = \App\Models\LeaveRequest::calcWorkingDays($start, $end, $holidayDates);

        if ($days < 1) {
            return back()->withErrors(['start_date' => 'Rentang tanggal tidak mengandung hari kerja (semua hari libur atau akhir pekan).'])->withInput();
        }

        // Check quota
        $leaveType = \App\Models\LeaveType::findOrFail($validated['leave_type_id']);
        if ($leaveType->max_days_per_year > 0) {
            $usedDays = \App\Models\LeaveRequest::where('employee_id', $employee->id)
                ->where('leave_type_id', $leaveType->id)
                ->where('status', 'approved')
                ->whereYear('start_date', $start->year)
                ->sum('days_count');
            if ($usedDays + $days > $leaveType->max_days_per_year) {
                return back()->withErrors(['leave_type_id' => "Kuota {$leaveType->name} tidak mencukupi. Sisa: " . ($leaveType->max_days_per_year - $usedDays) . " hari."])->withInput();
            }
        }

        $leave = \App\Models\LeaveRequest::create(array_merge($validated, [
            'employee_id' => $employee->id,
            'days_count'  => $days,
        ]));

        // Notify all HR/Admin staff
        $leave->load('leaveType');
        $staffUsers = User::whereNotIn('role', [\App\Enums\UserRole::Employee->value])->get();
        foreach ($staffUsers as $staff) {
            $staff->notify(new NewLeaveRequestNotification($leave));
        }

        return redirect()->route('my.leaves')->with('success', 'Permohonan cuti berhasil diajukan.');
    }

    public function showLeave(\App\Models\LeaveRequest $leaveRequest)
    {
        $this->authorizeEmployee();
        $employee = $this->getEmployee();
        abort_if($leaveRequest->employee_id !== $employee->id, 403);
        $leaveRequest->load(['leaveType', 'reviewer']);
        return view('employee-portal.leaves.show', compact('leaveRequest'));
    }

    public function destroyLeave(\App\Models\LeaveRequest $leaveRequest)
    {
        $this->authorizeEmployee();
        $employee = $this->getEmployee();
        abort_if($leaveRequest->employee_id !== $employee->id, 403);
        abort_if($leaveRequest->status !== 'pending', 422, 'Permohonan sudah diproses dan tidak dapat dibatalkan.');
        $leaveRequest->delete();
        return redirect()->route('my.leaves')->with('success', 'Permohonan cuti dibatalkan.');
    }

    // ── Announcements ─────────────────────────────────────────────────────────

    public function announcements()
    {
        $this->authorizeEmployee();
        $employee = $this->getEmployee()->load('company');

        $announcements = \App\Models\Announcement::with('author')
            ->visible()
            ->where(function ($q) use ($employee) {
                $q->whereNull('company_id')
                  ->orWhere('company_id', $employee->company_id);
            })
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->paginate(15);

        return view('employee-portal.announcements', compact('employee', 'announcements'));
    }

    // ── Internal Requests (Permohonan) ────────────────────────────────────────

    public function myRequests()
    {
        $this->authorizeEmployee();
        $employee = $this->getEmployee()->load('company');
        $requests = \App\Models\InternalRequest::where('employee_id', $employee->id)
            ->orderByDesc('created_at')
            ->paginate(15);
        return view('employee-portal.requests.index', compact('employee', 'requests'));
    }

    public function storeRequest(Request $request)
    {
        $this->authorizeEmployee();
        $employee = $this->getEmployee();

        $validated = $request->validate([
            'type'    => 'required|in:permohonan,pendanaan,surat_keterangan,pengaduan,lainnya',
            'subject' => 'required|string|max:200',
            'message' => 'required|string|min:10|max:3000',
        ]);

        $ireq = \App\Models\InternalRequest::create(array_merge($validated, [
            'employee_id' => $employee->id,
        ]));

        // Notify all HR/Admin staff
        $staffUsers = User::whereNotIn('role', [\App\Enums\UserRole::Employee->value])->get();
        foreach ($staffUsers as $staff) {
            $staff->notify(new NewInternalRequestNotification($ireq));
        }

        return redirect()->route('my.requests')->with('success', 'Permohonan berhasil dikirim.');
    }

    public function showRequest(\App\Models\InternalRequest $internalRequest)
    {
        $this->authorizeEmployee();
        $employee = $this->getEmployee();
        abort_if($internalRequest->employee_id !== $employee->id, 403);
        $internalRequest->load('responder');
        return view('employee-portal.requests.show', compact('internalRequest'));
    }
}

