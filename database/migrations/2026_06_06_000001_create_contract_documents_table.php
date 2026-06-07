<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_documents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('employee_id')->constrained()->cascadeOnDelete();
            $table->foreignId('created_by')->constrained('users')->restrictOnDelete();

            $table->string('contract_number');
            $table->date('contract_date')->nullable();
            $table->string('location')->nullable();

            $table->string('first_party_name')->nullable();
            $table->string('first_party_position')->nullable();
            $table->string('first_party_company')->nullable();
            $table->text('first_party_address')->nullable();

            $table->string('second_party_name')->nullable();
            $table->text('second_party_address')->nullable();
            $table->string('second_party_ktp')->nullable();

            $table->string('project_name')->nullable();
            $table->longText('scope_of_work')->nullable();

            $table->string('duration_text')->nullable();
            $table->date('start_date')->nullable();
            $table->date('end_date')->nullable();

            $table->decimal('contract_value', 18, 2)->nullable();
            $table->string('contract_value_text')->nullable();
            $table->string('payment_method')->default('Lump Sum');
            $table->longText('payment_terms')->nullable();

            $table->longText('rights_obligations')->nullable();
            $table->longText('hki_terms')->nullable();
            $table->longText('nda_terms')->nullable();
            $table->longText('sanctions_terms')->nullable();
            $table->longText('dispute_terms')->nullable();

            $table->string('bank_name')->nullable();
            $table->string('bank_account')->nullable();
            $table->string('bank_account_name')->nullable();

            $table->string('file_path')->nullable();
            $table->string('original_name')->nullable();
            $table->string('mime_type')->nullable();
            $table->unsignedBigInteger('file_size')->nullable();

            $table->longText('notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_documents');
    }
};