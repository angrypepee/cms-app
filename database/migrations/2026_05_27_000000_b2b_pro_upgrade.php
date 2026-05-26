<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── Payment history (multi-payment per invoice) ──
        Schema::create('invoice_payments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $t->date('payment_date');
            $t->decimal('amount', 18, 2);
            $t->string('method', 50)->nullable();
            $t->string('reference', 100)->nullable();
            $t->foreignId('bank_account_id')->nullable();
            $t->text('notes')->nullable();
            $t->foreignId('recorded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->index('invoice_id');
        });

        // ── Bank Accounts master (per Company) ──
        Schema::create('bank_accounts', function (Blueprint $t) {
            $t->id();
            $t->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $t->string('bank_name');
            $t->string('account_name');
            $t->string('account_number', 50);
            $t->string('branch')->nullable();
            $t->string('swift_code', 30)->nullable();
            $t->boolean('is_default')->default(false);
            $t->boolean('is_active')->default(true);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });

        // FK for invoice_payments.bank_account_id (declared after bank_accounts exists)
        Schema::table('invoice_payments', function (Blueprint $t) {
            $t->foreign('bank_account_id')->references('id')->on('bank_accounts')->nullOnDelete();
        });

        // ── Share tokens + timestamps on quotations ──
        Schema::table('quotations', function (Blueprint $t) {
            $t->string('share_token', 64)->nullable()->unique()->after('status');
            $t->timestamp('sent_at')->nullable()->after('share_token');
            $t->timestamp('viewed_at')->nullable()->after('sent_at');
        });

        // ── Share tokens + timestamps on invoices ──
        Schema::table('invoices', function (Blueprint $t) {
            $t->string('share_token', 64)->nullable()->unique()->after('status');
            $t->timestamp('sent_at')->nullable()->after('share_token');
            $t->timestamp('viewed_at')->nullable()->after('sent_at');
            $t->foreignId('bank_account_id')->nullable()->after('company_id')
              ->constrained('bank_accounts')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $t) {
            $t->dropConstrainedForeignId('bank_account_id');
            $t->dropColumn(['share_token','sent_at','viewed_at']);
        });
        Schema::table('quotations', function (Blueprint $t) {
            $t->dropColumn(['share_token','sent_at','viewed_at']);
        });
        Schema::dropIfExists('invoice_payments');
        Schema::dropIfExists('bank_accounts');
    }
};
