<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('train_sets', function (Blueprint $table) {
            $table->string('default_ktw_platform')->nullable()->after('default_berth_no');
            $table->string('default_end_station')->nullable()->after('default_ktw_platform');
            $table->string('default_end_no')->nullable()->after('default_end_station');
            $table->string('default_end_depot')->nullable()->after('default_end_no');
            $table->text('default_special_instructions')->nullable()->after('default_end_depot');
        });
    }

    public function down(): void
    {
        Schema::table('train_sets', function (Blueprint $table) {
            $table->dropColumn([
                'default_ktw_platform',
                'default_end_station',
                'default_end_no',
                'default_end_depot',
                'default_special_instructions',
            ]);
        });
    }
};