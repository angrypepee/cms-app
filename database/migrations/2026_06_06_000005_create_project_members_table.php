<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('project_members', function (Blueprint $table) {
            $table->id();
            $table->foreignId('project_id')->constrained('projects')->cascadeOnDelete();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->string('role', 100)->nullable()->comment('Peran dalam project, cth: Lead Developer, Designer');
            $table->text('notes')->nullable();
            $table->date('joined_at')->nullable();
            $table->timestamps();

            $table->unique(['project_id', 'employee_id']);
            $table->index('project_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('project_members');
    }
};
