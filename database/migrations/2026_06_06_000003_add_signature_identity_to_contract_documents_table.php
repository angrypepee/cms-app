<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_documents', function (Blueprint $table) {
            $table->string('signature_number')->nullable()->unique()->after('signed_at');
            $table->longText('signature_qr_data_uri')->nullable()->after('signature_number');
        });
    }

    public function down(): void
    {
        Schema::table('contract_documents', function (Blueprint $table) {
            $table->dropUnique('contract_documents_signature_number_unique');
            $table->dropColumn(['signature_number', 'signature_qr_data_uri']);
        });
    }
};