<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ProjectWorkHistory extends Model
{
    protected $fillable = [
        'project_id', 'employee_id', 'logged_by',
        'from_status', 'to_status', 'note',
    ];

    public function project()
    {
        return $this->belongsTo(Project::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function logger()
    {
        return $this->belongsTo(User::class, 'logged_by');
    }

    public function statusLabel(string $status): array
    {
        return \App\Models\Pivots\ProjectMemberPivot::workStatusLabel($status);
    }
}
