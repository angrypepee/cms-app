<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_documents', function (Blueprint $table) {
            // Store base salary from contract
            $table->decimal('base_salary', 18, 2)->nullable()->after('contract_value_text');
            // Store salary components (tunjangan & potongan) as JSON
            $table->json('salary_components')->nullable()->after('base_salary');
        });
    }

    public function down(): void
    {
        Schema::table('contract_documents', function (Blueprint $table) {
            $table->dropColumn(['base_salary', 'salary_components']);
        });
    }
};
