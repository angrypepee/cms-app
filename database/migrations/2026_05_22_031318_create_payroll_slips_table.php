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
        Schema::create('payroll_slips', function (Blueprint $table) {
            $table->id();
            $table->string('slip_number')->unique();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->integer('period_month');
            $table->integer('period_year');
            $table->date('cutoff_start')->nullable();
            $table->date('cutoff_end')->nullable();
            $table->date('payment_date')->nullable();
            $table->decimal('total_income', 15, 2)->default(0);
            $table->decimal('total_deduction', 15, 2)->default(0);
            $table->decimal('take_home_pay', 15, 2)->default(0);
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'published'])->default('draft');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payroll_slips');
    }
};
