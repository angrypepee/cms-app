<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class PayrollSlip extends Model
{
    use HasFactory;

    protected $fillable = [
        'slip_number', 'company_id', 'employee_id',
        'period_month', 'period_year',
        'cutoff_start', 'cutoff_end', 'payment_date', 'released_at',
        'total_income', 'total_deduction', 'take_home_pay',
        'notes', 'status',
        'signed_by', 'signed_at', 'employee_signed_at',
        'transfer_reference', 'transfer_bank', 'transfer_proof_path',
        'transfer_notes', 'transferred_at', 'transferred_by',
    ];

    protected $casts = [
        'cutoff_start'       => 'date',
        'cutoff_end'         => 'date',
        'payment_date'       => 'date',
        'released_at'        => 'date',
        'total_income'       => 'decimal:2',
        'total_deduction'    => 'decimal:2',
        'take_home_pay'      => 'decimal:2',
        'signed_at'          => 'datetime',
        'employee_signed_at' => 'datetime',
        'transferred_at'     => 'datetime',
    ];

    public function transferredBy()
    {
        return $this->belongsTo(\App\Models\User::class, 'transferred_by');
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function signer()
    {
        return $this->belongsTo(\App\Models\User::class, 'signed_by');
    }

    public function isSigned(): bool
    {
        return $this->signed_by !== null && $this->signed_at !== null;
    }

    public function isEmployeeSigned(): bool
    {
        return $this->employee_signed_at !== null;
    }

    public function items()
    {
        return $this->hasMany(PayrollItem::class)->orderBy('sort_order');
    }

    public function incomes()
    {
        return $this->hasMany(PayrollItem::class)->where('type', 'income')->orderBy('sort_order');
    }

    public function deductions()
    {
        return $this->hasMany(PayrollItem::class)->where('type', 'deduction')->orderBy('sort_order');
    }

    public function getPeriodLabelAttribute(): string
    {
        $months = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return ($months[$this->period_month] ?? $this->period_month) . ' ' . $this->period_year;
    }

    public static function generateSlipNumber(): string
    {
        $prefix = 'SG-' . date('Ymd');
        $last = self::where('slip_number', 'like', $prefix . '%')->latest('id')->first();
        $seq = $last ? ((int) substr($last->slip_number, -4)) + 1 : 1;
        return $prefix . str_pad($seq, 4, '0', STR_PAD_LEFT);
    }
}
