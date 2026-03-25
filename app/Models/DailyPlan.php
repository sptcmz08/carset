<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyPlan extends Model
{
    protected $fillable = [
        'plan_date', 'vehicle_id', 'driver_name', 'route',
        'departure_time', 'return_time', 'passengers', 'status', 'note',
    ];

    protected $casts = [
        'plan_date' => 'date',
        'departure_time' => 'datetime:H:i',
        'return_time' => 'datetime:H:i',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getStatusThaiAttribute(): string
    {
        return match ($this->status) {
            'planned' => 'วางแผนแล้ว',
            'in_progress' => 'กำลังวิ่ง',
            'completed' => 'เสร็จสิ้น',
            'cancelled' => 'ยกเลิก',
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'planned' => 'blue',
            'in_progress' => 'amber',
            'completed' => 'green',
            'cancelled' => 'red',
        };
    }
}
