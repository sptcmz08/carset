<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('train_sets', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->enum('default_consist_type', ['4', '6'])->default('6');
            $table->string('default_berth_no')->nullable();
            $table->unsignedSmallInteger('display_order')->unique();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('train_sets');
    }
};