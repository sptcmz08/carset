<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::table('train_sets')->where('code', 'T24')->update(['default_consist_type' => '4']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        //
    }
};
