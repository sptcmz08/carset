<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('train_set_mileage_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('train_set_id')->constrained()->cascadeOnDelete();
            $table->date('log_date');
            $table->unsignedInteger('mileage');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('train_set_mileage_logs');
    }
};