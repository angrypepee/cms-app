<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Hash;

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

    /**
     * Generate a slip number in the format:  SGLIM<YY><MM><####>
     *   - YY:  2-digit year of the slip period (defaults to current year)
     *   - MM:  2-digit month of the slip period (defaults to current month)
     *   - ####: 4-digit sequence, unique per (year, month)
     */
    public static function generateSlipNumber(?int $year = null, ?int $month = null): string
    {
        $year  = $year  ?? (int) date('Y');
        $month = $month ?? (int) date('n');

        $prefix = 'SGLIM' . str_pad((string) ($year % 100), 2, '0', STR_PAD_LEFT)
                          . str_pad((string) $month,       2, '0', STR_PAD_LEFT);

        $last = self::where('slip_number', 'like', $prefix . '%')->latest('id')->first();
        $seq  = $last ? ((int) substr($last->slip_number, -4)) + 1 : 1;

        return $prefix . str_pad((string) $seq, 4, '0', STR_PAD_LEFT);
    }

    /**
     * Return (and lazily create) the default signer account — "Finance LIM".
     * Used as the assigner on every slip.
     */
    public static function defaultSigner(): User
    {
        return User::firstOrCreate(
            ['email' => 'finance@lim.local'],
            [
                'name'      => 'Finance LIM',
                'title'     => 'Finance LIM',
                'role'      => UserRole::SignatureAdmin,
                'password'  => Hash::make(str()->random(32)),
                'is_active' => true,
            ]
        );
    }

    /**
     * Stamp default signatures on this slip whenever a signing date is known.
     * - Assigner  : Finance LIM (signed_by + signed_at)
     * - Employee  : auto-signed at the same effective date (employee_signed_at)
     *
     * Picks the effective date in this order: released_at → payment_date → now.
     * Only fills fields that are currently null; never overwrites existing values.
     */
    public function applyDefaultSignatures(): self
    {
        $effective = $this->released_at
            ?? $this->payment_date
            ?? Carbon::now();

        if ($effective instanceof \DateTimeInterface) {
            $effective = Carbon::instance($effective);
        } else {
            $effective = Carbon::parse($effective);
        }

        if ($this->signed_by === null) {
            $this->signed_by = static::defaultSigner()->id;
        }
        if ($this->signed_at === null) {
            $this->signed_at = $effective;
        }
        if ($this->employee_signed_at === null) {
            $this->employee_signed_at = $effective;
        }

        return $this;
    }
}
