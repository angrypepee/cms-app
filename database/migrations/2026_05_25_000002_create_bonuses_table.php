<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bonuses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();
            $table->enum('bonus_type', ['thr', 'project']);
            $table->string('title');
            $table->decimal('amount', 15, 2)->default(0);
            $table->integer('period_year');
            $table->tinyInteger('period_month')->nullable()->comment('1-12, mainly for THR');
            $table->date('payment_date')->nullable();
            $table->text('notes')->nullable();
            $table->enum('status', ['draft', 'paid'])->default('draft');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bonuses');
    }
};
