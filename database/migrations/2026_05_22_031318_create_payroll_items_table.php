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
        // Intentionally left empty — payroll_items is created by
        // 2026_05_22_031320_create_payroll_items_table.php which runs
        // after payroll_slips exists (required for the foreign key).
    }

    public function down(): void
    {
        // no-op
    }
};
