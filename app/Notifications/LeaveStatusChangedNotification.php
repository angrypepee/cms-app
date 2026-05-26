<?php

namespace App\Notifications;

use App\Models\LeaveRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to the employee when their leave request is approved or rejected.
 */
class LeaveStatusChangedNotification extends Notification
{
    use Queueable;

    public function __construct(public LeaveRequest $leaveRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $isApproved = $this->leaveRequest->status === 'approved';
        return [
            'type'        => 'leave_status_changed',
            'title'       => $isApproved ? 'Cuti Disetujui' : 'Cuti Ditolak',
            'message'     => 'Permohonan cuti ' . ($this->leaveRequest->leaveType->name ?? '') . ' Anda ' . ($isApproved ? 'telah disetujui.' : 'ditolak.' . ($this->leaveRequest->admin_notes ? ' Catatan: ' . $this->leaveRequest->admin_notes : '')),
            'url'         => route('my.leaves.show', $this->leaveRequest->id),
            'leave_id'    => $this->leaveRequest->id,
            'status'      => $this->leaveRequest->status,
        ];
    }
}
