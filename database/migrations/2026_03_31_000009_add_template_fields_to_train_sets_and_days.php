<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('train_sets', function (Blueprint $table) {
            $table->string('default_outbound_run_no')->nullable()->after('default_consist_type');
            $table->string('default_first_contact_plan')->nullable()->after('default_outbound_run_no');
            $table->string('default_departure_plan_time')->nullable()->after('default_first_contact_plan');
            $table->string('default_ktw_next_depart_time')->nullable()->after('default_ktw_platform');
            $table->string('default_inbound_run_no')->nullable()->after('default_ktw_next_depart_time');
            $table->string('default_end_time')->nullable()->after('default_end_station');
            $table->string('default_row_theme')->nullable()->after('default_special_instructions');
        });

        Schema::table('service_plan_days', function (Blueprint $table) {
            $table->json('note_blocks')->nullable()->after('footer_notes');
            $table->json('handover_blocks')->nullable()->after('note_blocks');
            $table->text('highlight_notice')->nullable()->after('handover_blocks');
        });

        Schema::table('service_plan_entries', function (Blueprint $table) {
            $table->string('row_theme')->nullable()->after('service_status');
        });
    }

    public function down(): void
    {
        Schema::table('service_plan_entries', function (Blueprint $table) {
            $table->dropColumn('row_theme');
        });

        Schema::table('service_plan_days', function (Blueprint $table) {
            $table->dropColumn(['note_blocks', 'handover_blocks', 'highlight_notice']);
        });

        Schema::table('train_sets', function (Blueprint $table) {
            $table->dropColumn([
                'default_outbound_run_no',
                'default_first_contact_plan',
                'default_departure_plan_time',
                'default_ktw_next_depart_time',
                'default_inbound_run_no',
                'default_end_time',
                'default_row_theme',
            ]);
        });
    }
};