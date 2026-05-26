<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payroll_slips', function (Blueprint $table) {
            $table->timestamp('employee_signed_at')->nullable()->after('signed_at');
        });
    }

    public function down(): void
    {
        Schema::table('payroll_slips', function (Blueprint $table) {
            $table->dropColumn('employee_signed_at');
        });
    }
};
