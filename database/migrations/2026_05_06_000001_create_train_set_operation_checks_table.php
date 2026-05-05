<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('train_set_operation_checks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('train_set_id')->constrained()->cascadeOnDelete();
            $table->string('category', 32);
            $table->string('check_key', 32);
            $table->string('status', 16)->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['train_set_id', 'category', 'check_key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('train_set_operation_checks');
    }
};
