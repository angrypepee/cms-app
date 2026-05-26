<?php

namespace App\Notifications;

use App\Models\InternalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to all HR/Admin users when an employee submits a new internal request.
 */
class NewInternalRequestNotification extends Notification
{
    use Queueable;

    public function __construct(public InternalRequest $internalRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $employee = $this->internalRequest->employee;
        return [
            'type'          => 'internal_request_new',
            'title'         => 'Permohonan Karyawan Baru',
            'message'       => ($employee->full_name ?? 'Karyawan') . ' mengajukan permohonan: ' . $this->internalRequest->subject,
            'url'           => route('internal-requests.show', $this->internalRequest->id),
            'request_id'    => $this->internalRequest->id,
            'employee_name' => $employee->full_name ?? '-',
            'request_type'  => $this->internalRequest->type,
        ];
    }
}
