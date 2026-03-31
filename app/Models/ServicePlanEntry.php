<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServicePlanEntry extends Model
{
    protected $fillable = [
        'service_plan_day_id',
        'train_set_id',
        'display_order',
        'service_status',
        'row_theme',
        'berth_no',
        'consist_type',
        'outbound_run_no',
        'first_contact_plan',
        'cab_one_time',
        'cab_four_six_time',
        'brake_test_time',
        'departure_plan_time',
        'departure_actual_time',
        'ktw_platform',
        'ktw_next_depart_time',
        'inbound_run_no',
        'end_station',
        'end_time',
        'end_no',
        'end_depot',
        'special_instructions',
    ];

    public function day(): BelongsTo
    {
        return $this->belongsTo(ServicePlanDay::class, 'service_plan_day_id');
    }

    public function trainSet(): BelongsTo
    {
        return $this->belongsTo(TrainSet::class);
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->service_status) {
            'available' => 'green',
            'warning' => 'yellow',
            'out_of_service' => 'red',
            default => 'gray',
        };
    }

    public function getStatusLabelAttribute(): string
    {
        return match ($this->service_status) {
            'available' => 'พร้อมให้บริการ',
            'warning' => 'ใกล้วาระซ่อม',
            'out_of_service' => 'งดให้บริการ',
            default => 'ไม่ระบุ',
        };
    }
}