<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('project_members', function (Blueprint $table) {
            $table->enum('work_status', ['not_started', 'in_progress', 'completed'])
                ->default('not_started')
                ->after('notes');
            $table->timestamp('work_started_at')->nullable()->after('work_status');
            $table->timestamp('work_completed_at')->nullable()->after('work_started_at');
        });
    }

    public function down(): void
    {
        Schema::table('project_members', function (Blueprint $table) {
            $table->dropColumn(['work_status', 'work_started_at', 'work_completed_at']);
        });
    }
};
