<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_plan_entries', function (Blueprint $table) {
            $table->id();
            $table->foreignId('service_plan_day_id')->constrained()->cascadeOnDelete();
            $table->foreignId('train_set_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedSmallInteger('display_order');
            $table->enum('service_status', ['available', 'warning', 'out_of_service'])->default('available');
            $table->string('berth_no')->nullable();
            $table->enum('consist_type', ['4', '6'])->nullable();
            $table->string('outbound_run_no')->nullable();
            $table->string('first_contact_plan')->nullable();
            $table->string('cab_one_time')->nullable();
            $table->string('cab_four_six_time')->nullable();
            $table->string('brake_test_time')->nullable();
            $table->string('departure_plan_time')->nullable();
            $table->string('departure_actual_time')->nullable();
            $table->string('ktw_platform')->nullable();
            $table->string('ktw_next_depart_time')->nullable();
            $table->string('inbound_run_no')->nullable();
            $table->string('end_station')->nullable();
            $table->string('end_time')->nullable();
            $table->string('end_no')->nullable();
            $table->string('end_depot')->nullable();
            $table->text('special_instructions')->nullable();
            $table->timestamps();

            $table->unique(['service_plan_day_id', 'display_order'], 'service_plan_entry_day_order_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_plan_entries');
    }
};