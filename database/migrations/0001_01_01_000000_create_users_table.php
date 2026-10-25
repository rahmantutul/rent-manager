<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('owners', function (Blueprint $table) {
            $table->id();
            $table->string('name', 150);
            $table->string('email');
            $table->string('phone');
            $table->char('currency_code', 3);
            $table->tinyInteger('minor_units');
            $table->string('timezone', 64);
            $table->string('locale', 10);
            $table->string('date_format', 20);
            $table->string('logo_path')->nullable();
            $table->enum('status', ['active', 'suspended']);
        });
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->index()->constrained()->restrictOnDelete();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password');
            $table->enum('role', ['super_admin', 'owner', 'manager', 'accountant', 'tenant']);
            $table->json('permissions')->nullable();
            $table->string('locale', 10)->nullable();
            $table->boolean('is_active');
            $table->timestamp('last_login_at')->nullable();
        });
        Schema::create('settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->nullable()->index()->constrained()->restrictOnDelete();
            $table->string('key', 100);
            $table->text('value');
            $table->unique(['owner_id', 'key']);
        });
        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });
        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sessions');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('settings');
        Schema::dropIfExists('users');
        Schema::dropIfExists('owners');
    }
};
