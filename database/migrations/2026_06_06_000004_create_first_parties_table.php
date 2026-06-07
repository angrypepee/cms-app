<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('first_parties', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('representative_name')->nullable();
            $table->string('representative_position')->nullable();
            $table->text('address')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        DB::table('first_parties')->insert([
            'name' => 'PT Lingkar Inovasi Muda',
            'representative_name' => 'Nama Penanggung Jawab',
            'representative_position' => 'Direktur',
            'address' => 'Alamat perusahaan pihak pertama.',
            'is_active' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function down(): void
    {
        Schema::dropIfExists('first_parties');
    }
};
