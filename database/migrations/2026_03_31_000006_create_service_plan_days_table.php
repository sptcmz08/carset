<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_plan_days', function (Blueprint $table) {
            $table->id();
            $table->date('service_date')->unique();
            $table->string('header_title')->default('Trainset Service Plan');
            $table->string('timetable_label')->default('Week Day (05:00 - 00:00)');
            $table->text('footer_notes')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_plan_days');
    }
};