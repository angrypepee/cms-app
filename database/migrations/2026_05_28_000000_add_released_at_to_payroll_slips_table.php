<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('payroll_slips', function (Blueprint $table) {
            if (!Schema::hasColumn('payroll_slips', 'released_at')) {
                $table->date('released_at')->nullable()->after('payment_date');
            }
        });
    }

    public function down(): void
    {
        Schema::table('payroll_slips', function (Blueprint $table) {
            if (Schema::hasColumn('payroll_slips', 'released_at')) {
                $table->dropColumn('released_at');
            }
        });
    }
};
