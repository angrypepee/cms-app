<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Company;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

/**
 * Production seeder — creates ONLY:
 *   1. A blank company record (edit details after first login)
 *   2. Super Admin account (full access)
 *   3. Admin account (admin role)
 *
 * Credentials are read from env vars (with safe defaults).
 * CHANGE PASSWORDS IMMEDIATELY AFTER FIRST LOGIN.
 *
 * Usage:
 *   php artisan db:seed --class=ProductionSeeder --force
 */
class ProductionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Minimal company placeholder
        Company::firstOrCreate(
            ['name' => env('SEED_COMPANY_NAME', 'My Company')],
            [
                'tagline' => '',
                'address' => '',
                'phone'   => '',
                'email'   => env('SEED_COMPANY_EMAIL', 'admin@example.com'),
                'npwp'    => '',
            ]
        );

        // 2. Super Admin
        User::firstOrCreate(
            ['email' => env('SEED_SUPERADMIN_EMAIL', 'superadmin@example.com')],
            [
                'name'     => 'Super Admin',
                'password' => Hash::make(env('SEED_SUPERADMIN_PASSWORD', 'ChangeMe!2026')),
                'role'     => UserRole::Admin->value,
                'title'    => 'Super Administrator',
            ]
        );

        // 3. Admin
        User::firstOrCreate(
            ['email' => env('SEED_ADMIN_EMAIL', 'admin@example.com')],
            [
                'name'     => 'Administrator',
                'password' => Hash::make(env('SEED_ADMIN_PASSWORD', 'ChangeMe!2026')),
                'role'     => UserRole::Admin->value,
                'title'    => 'Administrator',
            ]
        );

        $this->command->info('Production seed complete.');
        $this->command->warn('IMPORTANT: log in and change both admin passwords immediately.');
    }
}
