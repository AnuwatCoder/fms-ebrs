<?php

use App\Enums\EquipmentStatus;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('equipment_categories', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 50)->unique();
            $table->text('description')->nullable();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
        });

        Schema::create('equipment', function (Blueprint $table) {
            $table->id();
            $table->ulid('public_id')->unique();
            $table->foreignId('category_id')->constrained('equipment_categories')->restrictOnDelete();
            $table->string('equipment_code', 100)->unique();
            $table->string('asset_number', 100)->nullable()->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('brand')->nullable();
            $table->string('model')->nullable();
            $table->string('serial_number')->nullable()->unique();
            $table->string('location')->nullable();
            $table->date('purchase_date')->nullable();
            $table->string('image_path')->nullable();
            $table->string('status', 40)->default(EquipmentStatus::Available->value)->index();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();

            $table->index(['category_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('equipment');
        Schema::dropIfExists('equipment_categories');
    }
};
