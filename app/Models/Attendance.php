<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Attendance extends Model
{
    protected $fillable = [
        'employee_id',
        'date',
        'check_in',
        'check_out',
        'notes',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function employee(): BelongsTo
    {
        return $this->belongsTo(Employee::class);
    }

    /** True if the employee has checked in but not yet checked out. */
    public function isActive(): bool
    {
        return !is_null($this->check_in) && is_null($this->check_out);
    }

    /** Duration string (e.g. "6 j 23 m") or null if incomplete. */
    public function durationLabel(): ?string
    {
        if (!$this->check_in || !$this->check_out) {
            return null;
        }

        $in  = \Carbon\Carbon::parse($this->check_in);
        $out = \Carbon\Carbon::parse($this->check_out);
        $minutes = $out->diffInMinutes($in);

        $h = intdiv($minutes, 60);
        $m = $minutes % 60;

        return ($h > 0 ? "{$h} j " : '') . "{$m} m";
    }
}
