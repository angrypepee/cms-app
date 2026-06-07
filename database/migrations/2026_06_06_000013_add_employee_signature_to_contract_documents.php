<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_documents', function (Blueprint $table) {
            $table->foreignId('signed_by_employee')->nullable()->after('signed_at')
                ->constrained('users')->nullOnDelete();
            $table->timestamp('signed_at_employee')->nullable()->after('signed_by_employee');
            $table->string('signature_number_employee')->nullable()->unique()->after('signed_at_employee');
            $table->longText('signature_qr_employee')->nullable()->after('signature_number_employee');
        });
    }

    public function down(): void
    {
        Schema::table('contract_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('signed_by_employee');
            $table->dropColumn(['signed_at_employee', 'signature_number_employee', 'signature_qr_employee']);
        });
    }
};
