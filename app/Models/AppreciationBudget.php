<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AppreciationBudget extends Model
{
    protected $fillable = [
        'company_id', 'employee_id', 'created_by',
        'year', 'total_amount', 'notes',
    ];

    protected $casts = [
        'total_amount' => 'decimal:2',
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

    public function claims()
    {
        return $this->hasMany(AppreciationClaim::class)->latest();
    }

    public function approvedClaims()
    {
        return $this->hasMany(AppreciationClaim::class)->where('status', 'approved');
    }

    // ── Helpers ──────────────────────────────────────────────
    public function usedAmount(): float
    {
        return (float) $this->approvedClaims()->sum('amount');
    }

    public function remainingAmount(): float
    {
        return (float) $this->total_amount - $this->usedAmount();
    }

    public function usagePercentage(): int
    {
        if ((float) $this->total_amount <= 0) return 0;
        return (int) min(100, round(($this->usedAmount() / (float) $this->total_amount) * 100));
    }
}
