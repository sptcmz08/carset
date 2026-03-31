<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('train_sets', function (Blueprint $table) {
            $table->unsignedInteger('current_mileage')->default(0)->after('display_order');
            $table->unsignedInteger('next_service_mileage')->default(50000)->after('current_mileage');
            $table->date('last_maintenance_date')->nullable()->after('next_service_mileage');
            $table->date('next_maintenance_date')->nullable()->after('last_maintenance_date');
            $table->string('maintenance_status')->default('active')->after('next_maintenance_date');
            $table->text('repair_note')->nullable()->after('maintenance_status');
        });
    }

    public function down(): void
    {
        Schema::table('train_sets', function (Blueprint $table) {
            $table->dropColumn([
                'current_mileage',
                'next_service_mileage',
                'last_maintenance_date',
                'next_maintenance_date',
                'maintenance_status',
                'repair_note',
            ]);
        });
    }
};