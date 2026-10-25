<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tax_rates', function (Blueprint $t) {
            $t->id();
            $t->foreignId('owner_id')->constrained()->cascadeOnDelete();
            $t->string('name', 100);
            $t->decimal('rate', 6, 3);
            $t->boolean('is_inclusive')->default(false);
            $t->boolean('is_active')->default(true);
            $t->unique(['owner_id', 'name']);
        });
        Schema::create('charge_types', function (Blueprint $t) {
            $t->id();
            $t->foreignId('owner_id')->constrained()->cascadeOnDelete();
            $t->string('name', 100);
            $t->enum('kind', ['rent', 'service', 'utility', 'deposit', 'late_fee', 'other']);
            $t->unsignedBigInteger('default_tax_rate_id')->nullable();
            $t->boolean('is_system')->default(false);
            $t->boolean('is_active')->default(true);
            $t->unsignedSmallInteger('sort_order')->default(0);
            $t->unique(['owner_id', 'name']);
        });
        Schema::table('charge_types', function (Blueprint $t) {
            $t->foreign('default_tax_rate_id')->references('id')->on('tax_rates')->nullOnDelete();
        });
        Schema::create('late_fee_rules', function (Blueprint $t) {
            $t->id();
            $t->foreignId('owner_id')->constrained()->cascadeOnDelete();
            $t->string('name', 100);
            $t->enum('type', ['flat', 'percent_of_balance']);
            $t->bigInteger('flat_amount')->nullable();
            $t->decimal('percent', 6, 3)->nullable();
            $t->enum('repeat_every', ['once', 'week', 'month']);
            $t->bigInteger('max_amount')->nullable();
        });
        Schema::create('numbering_sequences', function (Blueprint $t) {
            $t->id();
            $t->foreignId('owner_id')->constrained()->cascadeOnDelete();
            $t->enum('doc_type', ['invoice', 'credit_note', 'payment', 'lease']);
            $t->string('prefix', 20);
            $t->string('format', 60);
            $t->unsignedBigInteger('next_number')->default(1);
            $t->enum('reset_policy', ['never', 'yearly', 'monthly'])->default('never');
            $t->date('last_reset_on')->nullable();
            $t->unique(['owner_id', 'doc_type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('numbering_sequences');
        Schema::dropIfExists('late_fee_rules');
        Schema::dropIfExists('charge_types');
        Schema::dropIfExists('tax_rates');
    }
};
