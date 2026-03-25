<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vehicles', function (Blueprint $table) {
            $table->id();
            $table->string('vehicle_code')->unique();
            $table->enum('vehicle_type', ['bus', 'van', 'minibus']);
            $table->string('license_plate')->unique();
            $table->string('brand');
            $table->string('model');
            $table->integer('capacity');
            $table->integer('current_mileage')->default(0);
            $table->integer('next_service_mileage')->default(10000);
            $table->date('last_maintenance_date')->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->enum('status', ['active', 'minor_repair', 'major_repair', 'retired'])->default('active');
            $table->text('repair_note')->nullable();
            $table->string('image_url')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vehicles');
    }
};
