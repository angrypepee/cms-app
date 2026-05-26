<?php

namespace App\Models;

use App\Enums\EmployeeCategory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Employee extends Model
{
    use HasFactory;

    protected $fillable = [
        'company_id', 'user_id', 'employee_id', 'name', 'position', 'department',
        'employee_category', 'grade', 'bank_name', 'bank_account', 'npwp',
        'bpjs_kesehatan', 'bpjs_ketenagakerjaan', 'is_active',
        'contract_start', 'contract_end', 'base_salary', 'salary_components',
    ];

    protected $casts = [
        'is_active'         => 'boolean',
        'employee_category' => EmployeeCategory::class,
        'contract_start'    => 'date',
        'contract_end'      => 'date',
        'base_salary'       => 'decimal:2',
        'salary_components' => 'array',
    ];

    /**
     * Return the agreed monthly salary structure:
     *  - 'Gaji Pokok' as a base income line
     *  - plus every saved salary_components row
     * Each entry: ['label' => string, 'type' => 'income'|'deduction', 'amount' => float]
     */
    public function salaryAgreement(): array
    {
        $base = (float) ($this->base_salary ?? 0);
        $rows = [[
            'label'  => 'Gaji Pokok',
            'type'   => 'income',
            'amount' => $base,
        ]];
        foreach ((array) ($this->salary_components ?? []) as $c) {
            $label  = trim((string) ($c['label']  ?? ''));
            $type   = ($c['type'] ?? 'income') === 'deduction' ? 'deduction' : 'income';
            $amount = (float) ($c['amount'] ?? 0);
            if ($label === '' || $amount <= 0) continue;
            $rows[] = ['label' => $label, 'type' => $type, 'amount' => $amount];
        }
        return $rows;
    }

    public function agreementTotals(): array
    {
        $income = 0; $deduction = 0;
        foreach ($this->salaryAgreement() as $r) {
            if ($r['type'] === 'income')    $income    += $r['amount'];
            if ($r['type'] === 'deduction') $deduction += $r['amount'];
        }
        return [
            'income'    => $income,
            'deduction' => $deduction,
            'take_home' => $income - $deduction,
        ];
    }

    /**
     * Auto-generate the next sequential employee ID in the format EMP-XXX (3+ digits).
     * Looks at the highest numeric suffix among existing employee_id values.
     */
    public static function generateEmployeeId(string $prefix = 'EMP-'): string
    {
        $max = static::where('employee_id', 'like', $prefix . '%')
            ->get(['employee_id'])
            ->map(fn($e) => (int) preg_replace('/\D/', '', substr((string) $e->employee_id, strlen($prefix))))
            ->max() ?? 0;

        return $prefix . str_pad((string) ($max + 1), 3, '0', STR_PAD_LEFT);
    }

    public function contractStatus(): string
    {
        if (!$this->contract_start) return 'none';
        $today = now()->startOfDay();
        if ($this->contract_end && $this->contract_end->lt($today)) return 'expired';
        if ($this->contract_end && $this->contract_end->lte($today->copy()->addDays(30))) return 'expiring';
        return 'active';
    }

    public function contractBadge(): array
    {
        return match($this->contractStatus()) {
            'expired'  => ['Kontrak Habis',  'danger'],
            'expiring' => ['Segera Berakhir','warning'],
            'active'   => ['Aktif',          'success'],
            default    => ['Permanen / -',   'secondary'],
        };
    }

    public function company()
    {
        return $this->belongsTo(Company::class);
    }

    public function user()
    {
        return $this->belongsTo(\App\Models\User::class, 'user_id');
    }

    public function hasLoginAccount(): bool
    {
        return !is_null($this->user_id);
    }

    public function payrollSlips()
    {
        return $this->hasMany(PayrollSlip::class);
    }

    public function documents()
    {
        return $this->hasMany(\App\Models\EmployeeDocument::class)->latest();
    }

    public function reimbursements()
    {
        return $this->hasMany(Reimbursement::class);
    }

    public function appreciationBudgets()
    {
        return $this->hasMany(\App\Models\AppreciationBudget::class);
    }

    public function leaveRequests()
    {
        return $this->hasMany(\App\Models\LeaveRequest::class);
    }

    public function internalRequests()
    {
        return $this->hasMany(\App\Models\InternalRequest::class);
    }
}
