<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        // ── B2B Clients ──
        Schema::create('clients', function (Blueprint $t) {
            $t->id();
            $t->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete()
              ->comment('Perusahaan internal yang menerbitkan');
            $t->string('name');
            $t->string('contact_person')->nullable();
            $t->string('email')->nullable();
            $t->string('phone', 50)->nullable();
            $t->string('npwp', 50)->nullable();
            $t->text('address')->nullable();
            $t->text('notes')->nullable();
            $t->boolean('is_active')->default(true);
            $t->timestamps();
            $t->index('name');
        });

        // ── Projects ──
        Schema::create('projects', function (Blueprint $t) {
            $t->id();
            $t->string('code', 50)->unique();
            $t->foreignId('client_id')->constrained('clients')->cascadeOnDelete();
            $t->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $t->string('name');
            $t->text('description')->nullable();
            $t->date('start_date')->nullable();
            $t->date('end_date')->nullable();
            $t->decimal('budget', 18, 2)->default(0);
            $t->enum('status', ['planning','active','on_hold','completed','cancelled'])->default('planning');
            $t->text('notes')->nullable();
            $t->timestamps();
            $t->index(['client_id','status']);
        });

        // ── Quotations ──
        Schema::create('quotations', function (Blueprint $t) {
            $t->id();
            $t->string('quotation_number', 60)->unique();
            $t->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $t->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $t->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $t->date('issue_date');
            $t->date('valid_until')->nullable();
            $t->string('subject')->nullable();
            $t->decimal('subtotal',    18, 2)->default(0);
            $t->decimal('discount',    18, 2)->default(0);
            $t->decimal('tax_percent', 5, 2)->default(0);
            $t->decimal('tax_amount',  18, 2)->default(0);
            $t->decimal('total',       18, 2)->default(0);
            $t->enum('status', ['draft','sent','accepted','rejected','expired','converted'])->default('draft');
            $t->text('notes')->nullable();
            $t->text('terms')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->index(['client_id','status']);
        });

        Schema::create('quotation_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('quotation_id')->constrained('quotations')->cascadeOnDelete();
            $t->string('description');
            $t->decimal('quantity',   12, 2)->default(1);
            $t->string('unit', 30)->nullable();
            $t->decimal('unit_price', 18, 2)->default(0);
            $t->decimal('amount',     18, 2)->default(0);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });

        // ── Invoices ──
        Schema::create('invoices', function (Blueprint $t) {
            $t->id();
            $t->string('invoice_number', 60)->unique();
            $t->foreignId('client_id')->constrained('clients')->restrictOnDelete();
            $t->foreignId('project_id')->nullable()->constrained('projects')->nullOnDelete();
            $t->foreignId('quotation_id')->nullable()->constrained('quotations')->nullOnDelete();
            $t->foreignId('company_id')->nullable()->constrained('companies')->nullOnDelete();
            $t->date('issue_date');
            $t->date('due_date')->nullable();
            $t->string('subject')->nullable();
            $t->decimal('subtotal',    18, 2)->default(0);
            $t->decimal('discount',    18, 2)->default(0);
            $t->decimal('tax_percent', 5, 2)->default(0);
            $t->decimal('tax_amount',  18, 2)->default(0);
            $t->decimal('total',       18, 2)->default(0);
            $t->decimal('paid_amount', 18, 2)->default(0);
            $t->enum('status', ['draft','sent','partial','paid','overdue','cancelled'])->default('draft');
            $t->date('payment_date')->nullable();
            $t->string('payment_reference', 100)->nullable();
            $t->string('payment_method', 50)->nullable();
            $t->text('notes')->nullable();
            $t->text('terms')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamps();
            $t->index(['client_id','status']);
        });

        Schema::create('invoice_items', function (Blueprint $t) {
            $t->id();
            $t->foreignId('invoice_id')->constrained('invoices')->cascadeOnDelete();
            $t->string('description');
            $t->decimal('quantity',   12, 2)->default(1);
            $t->string('unit', 30)->nullable();
            $t->decimal('unit_price', 18, 2)->default(0);
            $t->decimal('amount',     18, 2)->default(0);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_items');
        Schema::dropIfExists('invoices');
        Schema::dropIfExists('quotation_items');
        Schema::dropIfExists('quotations');
        Schema::dropIfExists('projects');
        Schema::dropIfExists('clients');
    }
};
