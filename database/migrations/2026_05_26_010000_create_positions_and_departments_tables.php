<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('positions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->string('description', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150)->unique();
            $table->string('description', 500)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        // Seed from existing employees (distinct values), plus common defaults
        $existingPositions = DB::table('employees')
            ->whereNotNull('position')->where('position', '!=', '')
            ->distinct()->pluck('position');
        $existingDepartments = DB::table('employees')
            ->whereNotNull('department')->where('department', '!=', '')
            ->distinct()->pluck('department');

        $defaultPositions = ['Manager', 'Supervisor', 'Staff', 'Software Engineer', 'HR Officer', 'Accountant'];
        $defaultDepartments = ['Engineering', 'Human Resources', 'Finance', 'Operations', 'Marketing'];

        $now = now();
        $positions = collect($existingPositions)->merge($defaultPositions)->unique()->filter()->values();
        foreach ($positions as $name) {
            DB::table('positions')->insertOrIgnore([
                'name' => $name, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }

        $departments = collect($existingDepartments)->merge($defaultDepartments)->unique()->filter()->values();
        foreach ($departments as $name) {
            DB::table('departments')->insertOrIgnore([
                'name' => $name, 'is_active' => true, 'created_at' => $now, 'updated_at' => $now,
            ]);
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('positions');
        Schema::dropIfExists('departments');
    }
};
