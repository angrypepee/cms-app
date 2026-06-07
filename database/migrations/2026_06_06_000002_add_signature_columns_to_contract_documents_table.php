<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_documents', function (Blueprint $table) {
            $table->foreignId('signed_by')->nullable()->after('bank_account_name')->constrained('users')->nullOnDelete();
            $table->timestamp('signed_at')->nullable()->after('signed_by');
        });
    }

    public function down(): void
    {
        Schema::table('contract_documents', function (Blueprint $table) {
            $table->dropConstrainedForeignId('signed_by');
            $table->dropColumn('signed_at');
        });
    }
};
