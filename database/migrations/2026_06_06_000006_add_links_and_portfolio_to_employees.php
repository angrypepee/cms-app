<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->string('github_url')->nullable()->after('bpjs_ketenagakerjaan');
            $table->string('gitlab_url')->nullable()->after('github_url');
            $table->string('linkedin_url')->nullable()->after('gitlab_url');
            $table->string('portfolio_url')->nullable()->after('linkedin_url');
        });

        Schema::create('employee_portfolios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained('employees')->cascadeOnDelete();
            $table->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('label');
            $table->string('file_path');
            $table->string('original_name');
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->timestamps();
            $table->index('employee_id');
        });
    }

    public function down(): void
    {
        Schema::table('employees', function (Blueprint $table) {
            $table->dropColumn(['github_url', 'gitlab_url', 'linkedin_url', 'portfolio_url']);
        });
        Schema::dropIfExists('employee_portfolios');
    }
};
