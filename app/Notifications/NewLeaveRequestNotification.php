<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to all HR/Admin users when an employee submits a new leave request.
 */
class NewLeaveRequestNotification extends Notification
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $employee = $this->leaveRequest->employee;
        return [
            'type'        => 'leave_request_new',
            'title'       => 'Permohonan Cuti Baru',
            'message'     => ($employee->full_name ?? 'Karyawan') . ' mengajukan cuti ' . ($this->leaveRequest->leaveType->name ?? '') . ' selama ' . $this->leaveRequest->days_count . ' hari.',
            'url'         => route('leaves.show', $this->leaveRequest->id),
            'leave_id'    => $this->leaveRequest->id,
            'employee_name' => $employee->full_name ?? '-',
        ];
    }
}
