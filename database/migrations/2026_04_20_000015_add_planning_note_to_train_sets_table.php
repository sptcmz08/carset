<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('train_sets', function (Blueprint $table) {
            $table->string('planning_note', 255)->nullable()->after('repair_note');
        });
    }

    public function down(): void
    {
        Schema::table('train_sets', function (Blueprint $table) {
            $table->dropColumn('planning_note');
        });
    }
};
