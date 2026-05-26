<?php

namespace App\Notifications;

use App\Models\InternalRequest;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

/**
 * Sent to the employee when an admin responds to their internal request.
 */
class InternalRequestRespondedNotification extends Notification
{
    use Queueable;

    public function __construct(public InternalRequest $internalRequest) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $statusMap = [
            'diproses' => 'sedang diproses',
            'selesai'  => 'telah diselesaikan',
            'ditolak'  => 'ditolak',
        ];
        $statusText = $statusMap[$this->internalRequest->status] ?? $this->internalRequest->status;

        return [
            'type'       => 'internal_request_responded',
            'title'      => 'Permohonan Anda Mendapat Balasan',
            'message'    => 'Permohonan "' . $this->internalRequest->subject . '" ' . $statusText . '.',
            'url'        => route('my.requests.show', $this->internalRequest->id),
            'request_id' => $this->internalRequest->id,
            'status'     => $this->internalRequest->status,
        ];
    }
}
