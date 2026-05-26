<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayrollItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'payroll_slip_id', 'type', 'label', 'amount', 'sort_order',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
    ];

    public function payrollSlip()
    {
        return $this->belongsTo(PayrollSlip::class);
    }
}
