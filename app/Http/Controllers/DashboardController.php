<?php

namespace App\Http\Controllers;

use App\Enums\UserRole;
use App\Models\AppreciationClaim;
use App\Models\Company;
use App\Models\Employee;
use App\Models\InternalRequest;
use App\Models\LeaveRequest;
use App\Models\OvertimeRequest;
use App\Models\PayrollSlip;
use App\Models\Reimbursement;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        if (auth()->user()->isEmployee()) {
            return redirect()->route('my.dashboard');
        }

        $user = auth()->user();

        // Pending reimbursements — admin/HR see all, others see only assigned to them
        $reimbBase = fn() => Reimbursement::where('status', 'pending')
            ->when(
                !$user->isAdmin() && $user->role !== UserRole::HR,
                fn($q) => $q->where('approver_id', $user->id)
            );

        $stats = [
            'companies'              => Company::count(),
            'employees'              => Employee::where('is_active', true)->count(),
            'slips_total'            => PayrollSlip::count(),
            'slips_draft'            => PayrollSlip::where('status', 'draft')->count(),
            'pending_reimbursements' => $reimbBase()->count(),
            'pending_claims'         => AppreciationClaim::where('status', 'pending')->count(),
            'pending_leaves'         => LeaveRequest::where('status', 'pending')->count(),
            'pending_requests'       => InternalRequest::where('status', 'pending')->count(),
            'pending_overtime'       => OvertimeRequest::where('status', 'pending')->count(),
        ];

        $recentSlips = PayrollSlip::with(['employee', 'company'])
            ->latest()
            ->take(8)
            ->get();

        $pendingReimbursements = $reimbBase()
            ->with(['employee', 'submitter'])
            ->latest()
            ->take(5)
            ->get();

        $pendingLeaves = LeaveRequest::with(['employee.company', 'leaveType'])
            ->where('status', 'pending')
            ->latest()
            ->take(6)
            ->get();

        $pendingRequests = InternalRequest::with('employee')
            ->where('status', 'pending')
            ->latest()
            ->take(6)
            ->get();

        return view('dashboard', compact(
            'stats', 'recentSlips', 'pendingReimbursements',
            'pendingLeaves', 'pendingRequests'
        ));
    }
}

