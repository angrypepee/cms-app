<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Bonus extends Model
{
    protected $fillable = [
        'company_id', 'employee_id', 'created_by',
        'bonus_type', 'title', 'amount',
        'period_year', 'period_month',
        'payment_date', 'notes', 'status',
    ];

    protected $casts = [
        'amount'       => 'decimal:2',
        'payment_date' => 'date',
    ];

    // ── Relationships ────────────────────────────────────────
    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function employee()
    {
        return $this->belongsTo(Employee::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    // ── Helpers ──────────────────────────────────────────────
    public function typeLabel(): string
    {
        return match($this->bonus_type) {
            'thr'     => 'Bonus Tahunan (THR)',
            'project' => 'Bonus Project',
            default   => $this->bonus_type,
        };
    }

    public function typeIcon(): string
    {
        return match($this->bonus_type) {
            'thr'     => 'bi-gift',
            'project' => 'bi-briefcase',
            default   => 'bi-cash-coin',
        };
    }

    public function typeBadgeColor(): string
    {
        return match($this->bonus_type) {
            'thr'     => 'warning',
            'project' => 'info',
            default   => 'secondary',
        };
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid';
    }

    public function periodLabel(): string
    {
        $months = [
            1=>'Jan',2=>'Feb',3=>'Mar',4=>'Apr',5=>'Mei',6=>'Jun',
            7=>'Jul',8=>'Agu',9=>'Sep',10=>'Okt',11=>'Nov',12=>'Des',
        ];
        if ($this->period_month) {
            return ($months[$this->period_month] ?? '') . ' ' . $this->period_year;
        }
        return (string) $this->period_year;
    }
}
