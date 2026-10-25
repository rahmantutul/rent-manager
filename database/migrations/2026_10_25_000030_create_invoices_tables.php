<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('invoices', function (Blueprint $t) {
            $t->id();
            $t->foreignId('owner_id')->constrained()->cascadeOnDelete();
            $t->string('invoice_number', 40);
            $t->enum('invoice_type', ['invoice', 'credit_note']);
            $t->enum('source', ['recurring', 'manual', 'late_fee', 'deposit']);
            $t->string('recurring_key', 80)->nullable()->unique();
            $t->foreignId('lease_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $t->foreignId('unit_id')->constrained()->restrictOnDelete();
            $t->foreignId('credit_for_invoice_id')->nullable()->constrained('invoices')->nullOnDelete();
            $t->date('period_start')->nullable();
            $t->date('period_end')->nullable();
            $t->date('issue_date');
            $t->date('due_date');
            $t->enum('status', ['draft', 'issued', 'void']);
            $t->enum('payment_status', ['unpaid', 'partial', 'paid'])->default('unpaid');
            $t->bigInteger('subtotal');
            $t->bigInteger('tax_total');
            $t->bigInteger('total');
            $t->bigInteger('amount_paid')->default(0);
            $t->bigInteger('balance_due');
            $t->char('currency_code', 3);
            $t->text('notes')->nullable();
            $t->timestamp('sent_at')->nullable();
            $t->timestamp('voided_at')->nullable();
            $t->string('void_reason')->nullable();
            $t->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $t->unique(['owner_id', 'invoice_number']);
        });
        Schema::create('invoice_lines', function (Blueprint $t) {
            $t->id();
            $t->foreignId('owner_id')->constrained()->cascadeOnDelete();
            $t->foreignId('invoice_id')->constrained()->cascadeOnDelete();
            $t->foreignId('charge_type_id')->nullable()->constrained()->nullOnDelete();
            $t->foreignId('lease_charge_id')->nullable()->constrained()->nullOnDelete();
            $t->string('description', 200);
            $t->decimal('quantity', 10, 2);
            $t->bigInteger('unit_price');
            $t->bigInteger('line_subtotal');
            $t->foreignId('tax_rate_id')->nullable()->constrained()->nullOnDelete();
            $t->decimal('tax_rate_value', 6, 3);
            $t->bigInteger('tax_amount');
            $t->bigInteger('line_total');
            $t->smallInteger('sort_order')->default(0);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('invoice_lines');
        Schema::dropIfExists('invoices');
    }
};
