<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('payroll_slips', function (Blueprint $table) {
            $table->foreignId('signed_by')->nullable()->constrained('users')->nullOnDelete()->after('status');
            $table->timestamp('signed_at')->nullable()->after('signed_by');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('payroll_slips', function (Blueprint $table) {
            $table->dropForeign(['signed_by']);
            $table->dropColumn(['signed_by', 'signed_at']);
        });
    }
};
