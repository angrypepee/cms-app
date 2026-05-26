<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $fillable = ['name', 'max_days_per_year', 'is_paid', 'color', 'is_active'];

    protected $casts = [
        'is_paid'          => 'boolean',
        'is_active'        => 'boolean',
        'max_days_per_year'=> 'integer',
    ];

    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }

    public function maxLabel(): string
    {
        return $this->max_days_per_year > 0
            ? "{$this->max_days_per_year} hari/tahun"
            : 'Tidak terbatas';
    }
}
