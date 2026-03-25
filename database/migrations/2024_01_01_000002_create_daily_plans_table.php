<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('daily_plans', function (Blueprint $table) {
            $table->id();
            $table->date('plan_date');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->string('driver_name');
            $table->string('route');
            $table->time('departure_time');
            $table->time('return_time')->nullable();
            $table->integer('passengers')->default(0);
            $table->enum('status', ['planned', 'in_progress', 'completed', 'cancelled'])->default('planned');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('daily_plans');
    }
};
