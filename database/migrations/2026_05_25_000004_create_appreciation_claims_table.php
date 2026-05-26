<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appreciation_claims', function (Blueprint $table) {
            $table->id();
            $table->foreignId('appreciation_budget_id')->constrained()->cascadeOnDelete();
            $table->foreignId('submitted_by')->constrained('users')->restrictOnDelete();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('title');
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2)->default(0)->comment('Requested amount');
            $table->enum('status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->text('rejection_reason')->nullable();
            $table->dateTime('reviewed_at')->nullable();
            $table->date('payment_date')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appreciation_claims');
    }
};
