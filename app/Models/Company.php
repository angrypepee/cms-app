<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Company extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'tagline', 'address', 'phone', 'email', 'logo', 'npwp',
        'work_start_time', 'work_end_time',
    ];

    public function employees()
    {
        return $this->hasMany(Employee::class);
    }

    public function payrollSlips()
    {
        return $this->hasMany(PayrollSlip::class);
    }
}
