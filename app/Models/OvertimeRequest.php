<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Carbon\Carbon;

class OvertimeRequest extends Model
{
    protected $fillable = [
        'employee_id', 'date', 'start_time', 'end_time',
        'reason', 'status', 'reviewed_by', 'reviewed_at', 'admin_notes',
    ];

    protected $casts = [
        'date'        => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function statusLabel(): string
    {
        return match($this->status) {
            'pending'  => 'Menunggu',
            'approved' => 'Disetujui',
            'rejected' => 'Ditolak',
            default    => $this->status,
        };
    }

    public function statusBadgeClass(): string
    {
        return match($this->status) {
            'pending'  => 'badge-pending',
            'approved' => 'badge-approved',
            'rejected' => 'badge-rejected',
            default    => 'badge-pending',
        };
    }

    /** Duration in minutes between start_time and end_time. */
    public function durationMinutes(): int
    {
        return Carbon::parse($this->start_time)->diffInMinutes(Carbon::parse($this->end_time));
    }

    /** Human-friendly duration string, e.g. "2 j 30 m". */
    public function durationLabel(): string
    {
        $mins = $this->durationMinutes();
        $h    = intdiv($mins, 60);
        $m    = $mins % 60;
        return ($h > 0 ? "{$h} j " : '') . "{$m} m";
    }
}
