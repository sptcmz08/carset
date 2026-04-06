<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('train_sets', function (Blueprint $table) {
            $table->unsignedInteger('minor_fault_count')->default(0)->after('maintenance_status');
            $table->unsignedInteger('major_fault_count')->default(0)->after('minor_fault_count');
            $table->boolean('overhaul_required')->default(false)->after('major_fault_count');
        });
    }

    public function down(): void
    {
        Schema::table('train_sets', function (Blueprint $table) {
            $table->dropColumn([
                'minor_fault_count',
                'major_fault_count',
                'overhaul_required',
            ]);
        });
    }
};
