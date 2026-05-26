<?php

namespace Database\Seeders;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\PayrollItem;
use App\Models\PayrollSlip;
use App\Models\User;
use App\Enums\UserRole;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

/**
 * Generates one year of dummy historical data:
 *  - Monthly published payroll slips (with admin + sometimes employee sign)
 *  - Daily attendance records (weekdays only) for each active employee
 *
 * Range: 12 months back from today, up to last completed month.
 */
class HistoricalDataSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', UserRole::Admin->value)->first()
              ?? User::where('role', UserRole::SignatureAdmin->value)->first();

        $employees = Employee::where('is_active', true)->get();
        if ($employees->isEmpty()) {
            $this->command?->warn('HistoricalDataSeeder: no active employees, skipping.');
            return;
        }

        // Ensure every active employee has a base_salary so slips can be auto-built.
        $defaultBases = [
            'EMP-001' => 12000000,
            'EMP-002' => 9500000,
            'EMP-003' => 6500000,
        ];
        foreach ($employees as $emp) {
            if (!$emp->base_salary || $emp->base_salary <= 0) {
                $emp->base_salary = $defaultBases[$emp->employee_id] ?? 7000000;
                $emp->save();
            }
        }

        // Build month list: 12 months back through last completed month.
        $now      = Carbon::now()->startOfMonth();
        $startMon = $now->copy()->subMonths(12);
        $endMon   = $now->copy()->subMonth(); // skip current (in-progress) month

        $months = [];
        for ($m = $startMon->copy(); $m->lte($endMon); $m->addMonth()) {
            $months[] = $m->copy();
        }

        $slipCount = 0;
        $attCount  = 0;

        DB::transaction(function () use ($months, $employees, $admin, &$slipCount, &$attCount) {
            foreach ($months as $month) {
                foreach ($employees as $emp) {
                    $slipCount += $this->seedSlip($emp, $month, $admin) ? 1 : 0;
                    $attCount  += $this->seedAttendance($emp, $month);
                }
            }
        });

        $this->command?->info("HistoricalDataSeeder: created {$slipCount} payroll slips & {$attCount} attendance rows.");
    }

    private function seedSlip(Employee $emp, Carbon $month, ?User $admin): bool
    {
        $exists = PayrollSlip::where('employee_id', $emp->id)
            ->where('period_month', $month->month)
            ->where('period_year', $month->year)
            ->exists();
        if ($exists) return false;

        $base = (float) $emp->base_salary;
        // Variation: random allowance + small deductions
        $tunjangan  = round($base * 0.10, -3);                 // 10% allowance, rounded to thousand
        $uangMakan  = 750000;
        $transport  = 500000;
        $income     = $base + $tunjangan + $uangMakan + $transport;

        $pph21      = round($base * 0.05, -3);
        $bpjsKes    = round($base * 0.01, -3);
        $bpjsTk     = round($base * 0.02, -3);
        $deduction  = $pph21 + $bpjsKes + $bpjsTk;

        $take       = $income - $deduction;

        $paymentDate = Carbon::create($month->year, $month->month, 25);
        $cutoffEnd   = $paymentDate->copy();
        $cutoffStart = $cutoffEnd->copy()->subMonthNoOverflow()->addDay();

        // Admin signs same day as payment; employee signs 1-3 days later (50% chance for older months, 80% for recent)
        $signedAt = $paymentDate->copy()->setTime(16, rand(0, 59));

        $monthsAgo = $paymentDate->diffInMonths(Carbon::now());
        $empSignChance = $monthsAgo <= 3 ? 80 : 50;
        $employeeSignedAt = (rand(1, 100) <= $empSignChance)
            ? $paymentDate->copy()->addDays(rand(1, 4))->setTime(rand(9, 17), rand(0, 59))
            : null;

        $slip = PayrollSlip::create([
            'slip_number'         => PayrollSlip::generateSlipNumber(),
            'company_id'          => $emp->company_id,
            'employee_id'         => $emp->id,
            'period_month'        => $month->month,
            'period_year'         => $month->year,
            'cutoff_start'        => $cutoffStart->toDateString(),
            'cutoff_end'          => $cutoffEnd->toDateString(),
            'payment_date'        => $paymentDate->toDateString(),
            'total_income'        => $income,
            'total_deduction'     => $deduction,
            'take_home_pay'       => $take,
            'notes'               => 'Dummy historical payroll',
            'status'              => 'published',
            'signed_by'           => $admin?->id,
            'signed_at'           => $signedAt,
            'employee_signed_at'  => $employeeSignedAt,
        ]);

        $incomes = [
            ['Gaji Pokok',          $base],
            ['Tunjangan Jabatan',   $tunjangan],
            ['Uang Makan',          $uangMakan],
            ['Tunjangan Transport', $transport],
        ];
        foreach ($incomes as $i => [$label, $amount]) {
            PayrollItem::create([
                'payroll_slip_id' => $slip->id,
                'type'            => 'income',
                'label'           => $label,
                'amount'          => $amount,
                'sort_order'      => $i,
            ]);
        }
        $deductions = [
            ['PPh 21',              $pph21],
            ['BPJS Kesehatan',      $bpjsKes],
            ['BPJS Ketenagakerjaan', $bpjsTk],
        ];
        foreach ($deductions as $i => [$label, $amount]) {
            PayrollItem::create([
                'payroll_slip_id' => $slip->id,
                'type'            => 'deduction',
                'label'           => $label,
                'amount'          => $amount,
                'sort_order'      => $i,
            ]);
        }

        return true;
    }

    private function seedAttendance(Employee $emp, Carbon $month): int
    {
        $count       = 0;
        $start       = $month->copy()->startOfMonth();
        $end         = $month->copy()->endOfMonth();

        for ($d = $start->copy(); $d->lte($end); $d->addDay()) {
            // weekdays only
            if ($d->isWeekend()) continue;

            // ~7% chance of absence (sick/leave)
            if (rand(1, 100) <= 7) continue;

            $exists = Attendance::where('employee_id', $emp->id)
                ->where('date', $d->toDateString())
                ->exists();
            if ($exists) continue;

            $checkInH  = 8;
            $checkInM  = rand(0, 45);          // 08:00 — 08:45
            $checkOutH = 17;
            $checkOutM = rand(0, 59);          // 17:00 — 17:59

            // ~10% chance of overtime (check-out 18-20)
            if (rand(1, 100) <= 10) {
                $checkOutH = rand(18, 20);
            }

            Attendance::create([
                'employee_id' => $emp->id,
                'date'        => $d->toDateString(),
                'check_in'    => sprintf('%02d:%02d:00', $checkInH, $checkInM),
                'check_out'   => sprintf('%02d:%02d:00', $checkOutH, $checkOutM),
                'notes'       => null,
            ]);
            $count++;
        }
        return $count;
    }
}
