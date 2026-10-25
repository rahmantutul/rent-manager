<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('properties', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained()->restrictOnDelete();
            $table->string('name', 150);
            $table->string('code', 30);
            $table->enum('type', ['residential', 'commercial', 'mixed']);
            $table->string('address_line1');
            $table->string('address_line2')->nullable();
            $table->string('city');
            $table->string('state')->nullable();
            $table->string('postal_code');
            $table->char('country_code', 2);
            $table->foreignId('manager_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->unique(['owner_id', 'code']);
            $table->index('owner_id');
        });

        Schema::create('buildings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained()->restrictOnDelete();
            $table->foreignId('property_id')->constrained()->restrictOnDelete();
            $table->string('name', 100);
            $table->smallInteger('floors_count')->nullable();
            $table->softDeletes();
            $table->index(['owner_id', 'property_id']);
        });

        Schema::create('units', function (Blueprint $table) {
            $table->id();
            $table->foreignId('owner_id')->constrained()->restrictOnDelete();
            $table->foreignId('building_id')->constrained()->restrictOnDelete();
            $table->string('unit_number', 30);
            $table->smallInteger('floor_level');
            $table->enum('unit_type', ['apartment', 'shop', 'office', 'parking', 'storage', 'other']);
            $table->unsignedTinyInteger('bedrooms')->nullable();
            $table->unsignedTinyInteger('bathrooms')->nullable();
            $table->decimal('area', 10, 2)->nullable();
            $table->enum('area_unit', ['sqft', 'sqm'])->nullable();
            $table->bigInteger('default_rent');
            $table->enum('status', ['vacant', 'occupied', 'maintenance', 'reserved']);
            $table->json('features')->nullable();
            $table->text('notes')->nullable();
            $table->softDeletes();
            $table->unique(['building_id', 'unit_number']);
            $table->index(['building_id', 'status']);
            $table->index('owner_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('units');
        Schema::dropIfExists('buildings');
        Schema::dropIfExists('properties');
    }
};
