<?php

namespace Database\Seeders;

use App\Models\User;
use App\Enums\UserRole;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     *
     * Demo accounts (all passwords: "password"):
     *   admin@payrolllim.test     — Administrator
     *   hr@payrolllim.test        — HRD / Staff
     *   signer@payrolllim.test    — Signature Admin
     *   test@example.com          — HRD / Staff (generic test account)
     */
    public function run(): void
    {
        $users = [
            [
                'name'     => 'Admin Utama',
                'email'    => 'admin@payrolllim.test',
                'password' => Hash::make('password'),
                'role'     => UserRole::Admin->value,
                'title'    => 'Head of HR',
            ],
            [
                'name'     => 'HR Staff',
                'email'    => 'hr@payrolllim.test',
                'password' => Hash::make('password'),
                'role'     => UserRole::HR->value,
                'title'    => 'HR Officer',
            ],
            [
                'name'     => 'Kepala Penandatangan',
                'email'    => 'signer@payrolllim.test',
                'password' => Hash::make('password'),
                'role'     => UserRole::SignatureAdmin->value,
                'title'    => 'Direktur Operasional',
            ],
            [
                'name'     => 'Test User',
                'email'    => 'test@example.com',
                'password' => Hash::make('password'),
                'role'     => UserRole::HR->value,
                'title'    => null,
            ],
        ];

        foreach ($users as $data) {
            User::firstOrCreate(['email' => $data['email']], $data);
        }

        $this->call(DemoSeeder::class);
        $this->call(HolidaySeeder::class);
        $this->call(HistoricalDataSeeder::class);
        $this->call(B2BDemoSeeder::class);
    }
}
