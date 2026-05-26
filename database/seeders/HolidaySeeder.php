<?php

namespace Database\Seeders;

use App\Models\Holiday;
use App\Models\LeaveType;
use Illuminate\Database\Seeder;

class HolidaySeeder extends Seeder
{
    public function run(): void
    {
        // ── Indonesian National Holidays 2026 ─────────────────────────────────
        $nationalHolidays = [
            ['date' => '2026-01-01', 'name' => 'Tahun Baru Masehi 2026'],
            ['date' => '2026-01-16', 'name' => 'Isra Mikraj Nabi Muhammad SAW'],
            ['date' => '2026-01-28', 'name' => 'Tahun Baru Imlek 2577'],
            ['date' => '2026-03-09', 'name' => 'Hari Raya Nyepi (Tahun Baru Saka 1948)'],
            ['date' => '2026-03-20', 'name' => 'Wafat Isa Al Masih'],
            ['date' => '2026-04-02', 'name' => 'Hari Raya Idul Fitri 1447 H'],
            ['date' => '2026-04-03', 'name' => 'Hari Raya Idul Fitri 1447 H (Hari Kedua)'],
            ['date' => '2026-04-05', 'name' => 'Paskah'],
            ['date' => '2026-05-01', 'name' => 'Hari Buruh Internasional'],
            ['date' => '2026-05-14', 'name' => 'Kenaikan Isa Al Masih'],
            ['date' => '2026-05-23', 'name' => 'Hari Raya Waisak 2570 BE'],
            ['date' => '2026-06-01', 'name' => 'Hari Lahir Pancasila'],
            ['date' => '2026-06-10', 'name' => 'Hari Raya Idul Adha 1447 H'],
            ['date' => '2026-07-01', 'name' => 'Tahun Baru Islam 1448 H'],
            ['date' => '2026-08-17', 'name' => 'Hari Kemerdekaan Republik Indonesia'],
            ['date' => '2026-09-09', 'name' => 'Maulid Nabi Muhammad SAW'],
            ['date' => '2026-12-25', 'name' => 'Hari Raya Natal'],
        ];

        foreach ($nationalHolidays as $h) {
            Holiday::updateOrCreate(
                ['date' => $h['date'], 'type' => 'national'],
                ['name' => $h['name'], 'type' => 'national', 'is_active' => true]
            );
        }

        // ── Default Leave Types ────────────────────────────────────────────────
        $leaveTypes = [
            ['name' => 'Cuti Tahunan',    'max_days_per_year' => 12,  'is_paid' => true,  'color' => '#2563eb'],
            ['name' => 'Cuti Sakit',      'max_days_per_year' => 0,   'is_paid' => true,  'color' => '#dc2626'],
            ['name' => 'Izin Tidak Masuk','max_days_per_year' => 5,   'is_paid' => false, 'color' => '#d97706'],
            ['name' => 'Cuti Melahirkan', 'max_days_per_year' => 90,  'is_paid' => true,  'color' => '#7c3aed'],
            ['name' => 'Cuti Duka',       'max_days_per_year' => 3,   'is_paid' => true,  'color' => '#475569'],
        ];

        foreach ($leaveTypes as $lt) {
            LeaveType::firstOrCreate(['name' => $lt['name']], $lt);
        }
    }
}
