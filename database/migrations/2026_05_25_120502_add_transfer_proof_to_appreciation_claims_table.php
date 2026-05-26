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
        Schema::table('appreciation_claims', function (Blueprint $table) {
            $table->string('transfer_proof_path')->nullable()->after('payment_date');
        });
    }

    public function down(): void
    {
        Schema::table('appreciation_claims', function (Blueprint $table) {
            $table->dropColumn('transfer_proof_path');
        });
    }
};
