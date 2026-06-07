<?php

namespace Database\Seeders;

use App\Models\ContractDocument;
use App\Models\User;
use Endroid\QrCode\Builder\Builder;
use Endroid\QrCode\Encoding\Encoding;
use Endroid\QrCode\ErrorCorrectionLevel;
use Endroid\QrCode\Writer\SvgWriter;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SignAllContractDocumentsSeeder extends Seeder
{
    public function run(): void
    {
        // Get admin user
        $admin = User::where('role', 'admin')->first();
        if (!$admin) {
            $admin = User::first(); // Fallback to first user
        }

        if (!$admin) {
            $this->command->error('No admin user found!');
            return;
        }

        $contracts = ContractDocument::all();
        $count = 0;

        foreach ($contracts as $contract) {
            // Skip if already fully signed
            if ($contract->isFullySigned()) {
                $this->command->info("Skipping contract {$contract->contract_number} - already fully signed");
                continue;
            }

            // Set date 1 day after contract_date
            $signDate = $contract->contract_date?->addDay();
            if (!$signDate) {
                $signDate = now();
            }

            // Sign as Pihak Pertama (Admin)
            if (!$contract->isSigned()) {
                $signatureNumber = $this->generateSignatureNumber($contract, $admin);
                $qrPayload = json_encode([
                    'type' => 'contract-signature',
                    'signature_number' => $signatureNumber,
                    'contract_id' => $contract->id,
                    'contract_number' => $contract->contract_number,
                    'signer_id' => $admin->id,
                    'signer_name' => $admin->name,
                    'signed_at' => $signDate->toIso8601String(),
                ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                $qrResult = (new Builder(new SvgWriter()))
                    ->build(
                        data: $qrPayload,
                        encoding: new Encoding('UTF-8'),
                        errorCorrectionLevel: ErrorCorrectionLevel::High,
                        size: 220,
                        margin: 8,
                    );

                $contract->update([
                    'signed_by' => $admin->id,
                    'signed_at' => $signDate,
                    'signature_number' => $signatureNumber,
                    'signature_qr_data_uri' => $qrResult->getDataUri(),
                ]);

                $this->command->info("✓ Signed P1: {$contract->contract_number}");
            }

            // Sign as Pihak Kedua (Employee)
            if (!$contract->isSignedByEmployee()) {
                $employee = $contract->employee;
                $employeeUser = $employee->user; // Get user from employee relationship

                if ($employeeUser) {
                    $signatureNumberEmployee = $this->generateSignatureNumber($contract, $employeeUser, 'employee');
                    $qrPayload = json_encode([
                        'type' => 'contract-signature',
                        'signature_number' => $signatureNumberEmployee,
                        'contract_id' => $contract->id,
                        'contract_number' => $contract->contract_number,
                        'signer_id' => $employeeUser->id,
                        'signer_name' => $employeeUser->name,
                        'signed_at' => $signDate->toIso8601String(),
                    ], JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

                    $qrResult = (new Builder(new SvgWriter()))
                        ->build(
                            data: $qrPayload,
                            encoding: new Encoding('UTF-8'),
                            errorCorrectionLevel: ErrorCorrectionLevel::High,
                            size: 220,
                            margin: 8,
                        );

                    $contract->update([
                        'signed_by_employee' => $employeeUser->id,
                        'signed_at_employee' => $signDate,
                        'signature_number_employee' => $signatureNumberEmployee,
                        'signature_qr_employee' => $qrResult->getDataUri(),
                    ]);

                    $this->command->info("✓ Signed P2: {$contract->contract_number} (Employee: {$employee->name})");
                } else {
                    $this->command->warn("⚠ No employee user found for {$employee->name} ({$contract->contract_number})");
                }
            }

            $count++;
        }

        $this->command->info("\n✅ Successfully signed {$count} contract(s)!");
    }

    private function generateSignatureNumber(ContractDocument $contract, User $user, string $type = 'admin'): string
    {
        $timestamp = now()->format('YmdHis');
        $userId = str_pad($user->id, 4, '0', STR_PAD_LEFT);
        $contractId = str_pad($contract->id, 4, '0', STR_PAD_LEFT);
        $random = strtoupper(Str::random(4));

        return "SIG-{$type}-{$timestamp}-{$userId}-{$contractId}-{$random}";
    }
}
