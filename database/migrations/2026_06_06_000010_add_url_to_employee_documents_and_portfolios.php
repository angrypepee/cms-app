<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->string('url')->nullable()->after('file_size')
                ->comment('Alternatif link jika dokumen berbentuk URL bukan file');
        });

        Schema::table('employee_portfolios', function (Blueprint $table) {
            $table->string('url')->nullable()->after('file_size')
                ->comment('Alternatif link jika portfolio berbentuk URL bukan file');
            // Make file_path nullable so link-only entries are valid
            $table->string('original_name')->nullable()->change();
            $table->string('file_path')->nullable()->change();
        });
    }

    public function down(): void
    {
        Schema::table('employee_documents', function (Blueprint $table) {
            $table->dropColumn('url');
        });
        Schema::table('employee_portfolios', function (Blueprint $table) {
            $table->dropColumn('url');
        });
    }
};
