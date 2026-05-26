<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payroll_slips', function (Blueprint $table) {
            $table->string('transfer_reference', 100)->nullable()->after('notes')
                ->comment('Nomor referensi/transaksi dari bank');
            $table->string('transfer_bank', 100)->nullable()->after('transfer_reference')
                ->comment('Nama bank pengirim');
            $table->string('transfer_proof_path', 500)->nullable()->after('transfer_bank')
                ->comment('Path file bukti transfer (struk/screenshot)');
            $table->text('transfer_notes')->nullable()->after('transfer_proof_path');
            $table->timestamp('transferred_at')->nullable()->after('transfer_notes');
            $table->foreignId('transferred_by')->nullable()->after('transferred_at')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('payroll_slips', function (Blueprint $table) {
            $table->dropForeign(['transferred_by']);
            $table->dropColumn([
                'transfer_reference', 'transfer_bank', 'transfer_proof_path',
                'transfer_notes', 'transferred_at', 'transferred_by',
            ]);
        });
    }
};
