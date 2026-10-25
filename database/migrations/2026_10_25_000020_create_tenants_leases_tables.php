<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tenants', function (Blueprint $t) {
            $t->id();
            $t->foreignId('owner_id')->constrained()->cascadeOnDelete();
            $t->enum('type', ['individual', 'company']);
            $t->string('name', 150);
            $t->string('company_name', 150)->nullable();
            $t->string('email')->nullable();
            $t->string('phone')->nullable();
            $t->string('alt_phone')->nullable();
            $t->string('id_document_type', 40)->nullable();
            $t->text('id_document_number')->nullable();
            $t->date('date_of_birth')->nullable();
            $t->string('emergency_contact_name')->nullable();
            $t->string('emergency_contact_phone')->nullable();
            $t->text('previous_address')->nullable();
            $t->enum('status', ['prospect', 'active', 'former']);
            $t->boolean('portal_enabled')->default(false);
            $t->text('notes')->nullable();
            $t->softDeletes();
            $t->index(['owner_id', 'status']);
        });
        Schema::create('leases', function (Blueprint $t) {
            $t->id();
            $t->foreignId('owner_id')->constrained()->cascadeOnDelete();
            $t->string('lease_number', 40);
            $t->foreignId('unit_id')->constrained()->restrictOnDelete();
            $t->foreignId('tenant_id')->constrained()->restrictOnDelete();
            $t->date('start_date');
            $t->date('end_date')->nullable();
            $t->date('move_out_date')->nullable();
            $t->enum('status', ['draft', 'active', 'ended', 'terminated'])->default('draft');
            $t->unsignedBigInteger('active_unit_id')->nullable();
            $t->enum('billing_cycle', ['monthly', 'quarterly', 'semiannual', 'yearly']);
            $t->unsignedTinyInteger('billing_day');
            $t->smallInteger('due_after_days')->default(0);
            $t->smallInteger('grace_days')->default(0);
            $t->date('next_invoice_date')->nullable();
            $t->boolean('auto_invoice')->default(true);
            $t->boolean('prorate_first_period')->default(false);
            $t->bigInteger('security_deposit')->default(0);
            $t->foreignId('late_fee_rule_id')->nullable()->constrained()->nullOnDelete();
            $t->smallInteger('notice_period_days')->nullable();
            $t->text('termination_reason')->nullable();
            $t->text('terms')->nullable();
            $t->unique(['owner_id', 'lease_number']);
            $t->unique('active_unit_id');
        });
        Schema::create('lease_charges', function (Blueprint $t) {
            $t->id();
            $t->foreignId('owner_id')->constrained()->cascadeOnDelete();
            $t->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $t->foreignId('charge_type_id')->constrained()->restrictOnDelete();
            $t->string('description', 150)->nullable();
            $t->bigInteger('amount');
            $t->enum('frequency', ['every_cycle', 'monthly', 'quarterly', 'yearly', 'once']);
            $t->date('starts_on');
            $t->date('ends_on')->nullable();
            $t->foreignId('tax_rate_id')->nullable()->constrained()->nullOnDelete();
            $t->boolean('is_active')->default(true);
        });
        Schema::create('lease_tenants', function (Blueprint $t) {
            $t->foreignId('lease_id')->constrained()->cascadeOnDelete();
            $t->foreignId('tenant_id')->constrained()->cascadeOnDelete();
            $t->decimal('share_percent', 5, 2)->nullable();
            $t->primary(['lease_id', 'tenant_id']);
        });
        Schema::create('attachments', function (Blueprint $t) {
            $t->id();
            $t->foreignId('owner_id')->constrained()->cascadeOnDelete();
            $t->string('attachable_type');
            $t->unsignedBigInteger('attachable_id');
            $t->string('disk');
            $t->string('path');
            $t->string('original_name');
            $t->string('mime');
            $t->unsignedBigInteger('size');
            $t->foreignId('uploaded_by')->nullable()->constrained('users')->nullOnDelete();
            $t->timestamp('created_at')->useCurrent();
            $t->index(['attachable_type', 'attachable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attachments');
        Schema::dropIfExists('lease_tenants');
        Schema::dropIfExists('lease_charges');
        Schema::dropIfExists('leases');
        Schema::dropIfExists('tenants');
    }
};
