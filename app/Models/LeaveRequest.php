<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveRequest extends Model
{
    protected $fillable = [
        'employee_id', 'leave_type_id', 'start_date', 'end_date', 'days_count',
        'reason', 'status', 'reviewed_by', 'reviewed_at', 'admin_notes',
    ];

    protected $casts = [
        'start_date'  => 'date',
        'end_date'    => 'date',
        'reviewed_at' => 'datetime',
    ];

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function leaveType()
    {
        return $this->belongsTo(LeaveType::class);
    }

    public function reviewer()
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

    /** Calculate working days (Mon–Fri, excluding national/company holidays) between two dates */
    public static function calcWorkingDays(\Carbon\Carbon $start, \Carbon\Carbon $end, array $holidayDates = []): int
    {
        $days = 0;
        $current = $start->copy();
        while ($current->lte($end)) {
            $dow = $current->dayOfWeek; // 0=Sun, 6=Sat
            if ($dow !== 0 && $dow !== 6 && !in_array($current->toDateString(), $holidayDates)) {
                $days++;
            }
            $current->addDay();
        }
        return $days;
    }
}
