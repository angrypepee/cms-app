<?php

namespace Database\Seeders;

use App\Models\Company;
use App\Models\Employee;
use App\Models\PayrollItem;
use App\Models\PayrollSlip;
use Illuminate\Database\Seeder;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        // Company
        $company = Company::create([
            'name'    => 'PT Contoh Makmur Sejahtera',
            'tagline' => 'Berkembang Bersama, Maju Bersama',
            'address' => 'Jl. Sudirman No. 123, Jakarta Selatan 12190',
            'phone'   => '021-5551234',
            'email'   => 'hrd@contohmakmur.co.id',
            'npwp'    => '01.234.567.8-901.000',
        ]);

        // Employees
        $employees = [
            [
                'company_id'           => $company->id,
                'employee_id'          => 'EMP-001',
                'name'                 => 'Budi Santoso',
                'position'             => 'Senior Developer',
                'department'           => 'Technology',
                'grade'                => 'IV-A',
                'bank_name'            => 'BCA',
                'bank_account'         => '1234567890',
                'npwp'                 => '12.345.678.9-001.000',
                'bpjs_kesehatan'       => '0001234567890',
                'bpjs_ketenagakerjaan' => '14012345678901',
                'is_active'            => true,
            ],
            [
                'company_id'           => $company->id,
                'employee_id'          => 'EMP-002',
                'name'                 => 'Siti Rahma',
                'position'             => 'HR Manager',
                'department'           => 'Human Resources',
                'grade'                => 'III-B',
                'bank_name'            => 'Mandiri',
                'bank_account'         => '9876543210',
                'npwp'                 => '98.765.432.1-001.000',
                'bpjs_kesehatan'       => '0009876543210',
                'bpjs_ketenagakerjaan' => '14098765432100',
                'is_active'            => true,
            ],
            [
                'company_id'           => $company->id,
                'employee_id'          => 'EMP-003',
                'name'                 => 'Ahmad Fauzi',
                'position'             => 'Finance Staff',
                'department'           => 'Finance',
                'grade'                => 'II-A',
                'bank_name'            => 'BNI',
                'bank_account'         => '5555444433',
                'npwp'                 => null,
                'bpjs_kesehatan'       => '0005555444433',
                'bpjs_ketenagakerjaan' => '14055554444330',
                'is_active'            => true,
            ],
        ];

        foreach ($employees as $emp) {
            Employee::create($emp);
        }

        // Payroll Slip for Budi
        $budi = Employee::where('employee_id', 'EMP-001')->first();
        $slip = PayrollSlip::create([
            'slip_number'     => PayrollSlip::generateSlipNumber(),
            'company_id'      => $company->id,
            'employee_id'     => $budi->id,
            'period_month'    => 5,
            'period_year'     => 2026,
            'cutoff_start'    => '2026-05-01',
            'cutoff_end'      => '2026-05-31',
            'payment_date'    => '2026-05-31',
            'total_income'    => 12500000,
            'total_deduction' => 1375000,
            'take_home_pay'   => 11125000,
            'notes'           => null,
            'status'          => 'published',
        ]);

        $incomeItems = [
            ['label' => 'Gaji Pokok',         'amount' => 10000000],
            ['label' => 'Tunjangan Jabatan',   'amount' => 1500000],
            ['label' => 'Uang Makan',          'amount' => 750000],
            ['label' => 'Tunjangan Transport', 'amount' => 250000],
        ];
        $deductionItems = [
            ['label' => 'PPh 21',              'amount' => 875000],
            ['label' => 'BPJS Kesehatan',      'amount' => 250000],
            ['label' => 'BPJS Ketenagakerjaan','amount' => 250000],
        ];

        foreach ($incomeItems as $i => $item) {
            PayrollItem::create(['payroll_slip_id' => $slip->id, 'type' => 'income',    'label' => $item['label'], 'amount' => $item['amount'], 'sort_order' => $i]);
        }
        foreach ($deductionItems as $i => $item) {
            PayrollItem::create(['payroll_slip_id' => $slip->id, 'type' => 'deduction', 'label' => $item['label'], 'amount' => $item['amount'], 'sort_order' => $i]);
        }

        // Draft slip for Siti
        $siti  = Employee::where('employee_id', 'EMP-002')->first();
        $slip2 = PayrollSlip::create([
            'slip_number'     => PayrollSlip::generateSlipNumber(),
            'company_id'      => $company->id,
            'employee_id'     => $siti->id,
            'period_month'    => 5,
            'period_year'     => 2026,
            'cutoff_start'    => '2026-05-01',
            'cutoff_end'      => '2026-05-31',
            'payment_date'    => '2026-05-31',
            'total_income'    => 9500000,
            'total_deduction' => 950000,
            'take_home_pay'   => 8550000,
            'notes'           => null,
            'status'          => 'draft',
        ]);

        $incomeItems2 = [
            ['label' => 'Gaji Pokok',   'amount' => 8000000],
            ['label' => 'Uang Makan',   'amount' => 750000],
            ['label' => 'Tunjangan Komunikasi', 'amount' => 750000],
        ];
        $deductionItems2 = [
            ['label' => 'PPh 21',         'amount' => 600000],
            ['label' => 'BPJS Kesehatan', 'amount' => 200000],
            ['label' => 'BPJS Ketenagakerjaan', 'amount' => 150000],
        ];
        foreach ($incomeItems2 as $i => $item) {
            PayrollItem::create(['payroll_slip_id' => $slip2->id, 'type' => 'income',    'label' => $item['label'], 'amount' => $item['amount'], 'sort_order' => $i]);
        }
        foreach ($deductionItems2 as $i => $item) {
            PayrollItem::create(['payroll_slip_id' => $slip2->id, 'type' => 'deduction', 'label' => $item['label'], 'amount' => $item['amount'], 'sort_order' => $i]);
        }
    }
}
