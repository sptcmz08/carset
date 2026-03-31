<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('train_set_maintenance_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('train_set_id')->constrained()->cascadeOnDelete();
            $table->string('maintenance_type');
            $table->text('description');
            $table->decimal('cost', 10, 2)->default(0);
            $table->unsignedInteger('mileage_at_service')->default(0);
            $table->date('service_date');
            $table->date('completed_date')->nullable();
            $table->string('status')->default('pending');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('train_set_maintenance_logs');
    }
};