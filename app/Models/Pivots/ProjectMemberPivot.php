<?php

namespace App\Models\Pivots;

use Illuminate\Database\Eloquent\Relations\Pivot;

class ProjectMemberPivot extends Pivot
{
    protected $casts = [
        'joined_at'          => 'date',
        'work_started_at'    => 'datetime',
        'work_completed_at'  => 'datetime',
    ];

    public static function workStatusLabel(string $status): array
    {
        return match($status) {
            'in_progress' => ['Sedang Dikerjakan', 'primary'],
            'completed'   => ['Selesai', 'success'],
            default       => ['Belum Dimulai', 'secondary'],
        };
    }
}
