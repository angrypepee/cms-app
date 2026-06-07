<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contract_documents', function (Blueprint $table) {
            // Signatory from first party (HRD / authorized person of PT LIM)
            $table->string('penandatangan_p1_name')->nullable()->after('first_party_address')
                ->comment('Nama penandatangan pihak pertama (default dari master first parties)');
            $table->string('penandatangan_p1_position')->nullable()->after('penandatangan_p1_name')
                ->comment('Jabatan penandatangan pihak pertama');
        });
    }

    public function down(): void
    {
        Schema::table('contract_documents', function (Blueprint $table) {
            $table->dropColumn(['penandatangan_p1_name', 'penandatangan_p1_position']);
        });
    }
};
