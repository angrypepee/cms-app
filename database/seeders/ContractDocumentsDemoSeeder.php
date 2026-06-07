<?php

namespace Database\Seeders;

use App\Models\ContractDocument;
use App\Models\Employee;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Carbon;

class ContractDocumentsDemoSeeder extends Seeder
{
    public function run(): void
    {
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $employees = Employee::whereNotNull('name')->take(3)->get();

        if (!$admin || $employees->isEmpty()) {
            $this->command->warn('ContractDocumentsDemoSeeder dilewati: tidak ada admin atau employees.');
            return;
        }

        $today = Carbon::today();
        $contractBaseNumber = 234;

        foreach ($employees as $index => $employee) {
            $month = now()->format('m');
            $year = now()->format('Y');
            $contractNumber = ($contractBaseNumber + $index) . '/SPK/LIM/' . $month . '/' . $year;

            ContractDocument::create([
                'employee_id' => $employee->id,
                'created_by' => $admin->id,
                'contract_number' => $contractNumber,
                'contract_date' => $today->copy()->subDays($index + 2),
                'location' => 'Jakarta',
                'first_party_name' => 'PT Contoh Makmur Sejahtera',
                'first_party_position' => 'Direktur',
                'first_party_company' => 'PT Contoh Makmur Sejahtera',
                'first_party_address' => 'Jl. Sudirman No. 123, Jakarta Selatan 12190',
                'second_party_name' => $employee->name,
                'second_party_address' => 'Jl. Contoh No. ' . ($index + 1),
                'second_party_ktp' => '3275' . str_pad((string)($index + 1), 12, '0', STR_PAD_LEFT),
                'project_name' => 'Kontrak Kerja ' . $employee->name,
                'scope_of_work' => 'Menjalankan tugas dan tanggung jawab sesuai dengan posisi yang ditetapkan.',
                'duration_text' => '1 tahun',
                'start_date' => $today->copy()->subDays($index + 1),
                'end_date' => $today->copy()->addYear()->subDays($index + 1),
                'contract_value' => 0,
                'contract_value_text' => 'Sesuai dengan gaji pokok yang ditetapkan',
                'payment_method' => 'Bank Transfer',
                'payment_terms' => 'Bulanan',
                'base_salary' => 0,
                'salary_components' => null,
                'rights_obligations' => 'Pihak kedua berhak mendapatkan gaji, tunjangan, dan benefit sesuai kebijakan perusahaan.',
                'hki_terms' => 'Semua karya intelektual menjadi milik perusahaan.',
                'nda_terms' => 'Pihak kedua wajib menjaga kerahasiaan data perusahaan.',
                'sanctions_terms' => 'Pelanggaran dapat mengakibatkan sanksi sesuai peraturan yang berlaku.',
                'dispute_terms' => 'Sengketa diselesaikan secara musyawarah.',
                'bank_name' => 'BCA',
                'bank_account' => '1234567890',
                'bank_account_name' => $employee->name,
                'penandatangan_p1_name' => 'Direktur',
                'penandatangan_p1_position' => 'Direktur Utama',
                'status' => 'active',
            ]);

            $this->command->info("✓ Kontrak {$contractNumber} dibuat untuk {$employee->name}");
        }

        $this->command->info("Selesai: {$employees->count()} kontrak demo berhasil dibuat.");
    }
}
