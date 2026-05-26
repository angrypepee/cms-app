<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appreciation_budgets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->integer('year');
            $table->decimal('total_amount', 15, 2)->default(0)->comment('Total annual appreciation budget');
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->unique(['employee_id', 'year'], 'unique_employee_year_budget');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appreciation_budgets');
    }
};
