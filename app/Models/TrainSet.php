<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TrainSet extends Model
{
    protected $fillable = [
        'code',
        'default_consist_type',
        'default_outbound_run_no',
        'default_first_contact_plan',
        'default_departure_plan_time',
        'default_berth_no',
        'default_ktw_platform',
        'default_ktw_next_depart_time',
        'default_inbound_run_no',
        'default_end_station',
        'default_end_time',
        'default_end_no',
        'default_end_depot',
        'default_special_instructions',
        'default_row_theme',
        'display_order',
    ];

    public function servicePlanEntries(): HasMany
    {
        return $this->hasMany(ServicePlanEntry::class);
    }
}