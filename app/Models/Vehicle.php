<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Vehicle extends Model
{
    protected $fillable = [
        'vehicle_code', 'vehicle_type', 'license_plate', 'brand', 'model',
        'capacity', 'current_mileage', 'next_service_mileage',
        'last_maintenance_date', 'next_maintenance_date',
        'status', 'repair_note', 'image_url',
    ];

    protected $casts = [
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
    ];

    public function dailyPlans()
    {
        return $this->hasMany(DailyPlan::class);
    }

    public function maintenanceLogs()
    {
        return $this->hasMany(MaintenanceLog::class);
    }

    public function mileageLogs()
    {
        return $this->hasMany(MileageLog::class);
    }

    /**
     * Get health status: green, yellow, orange, red
     */
    public function getHealthStatusAttribute(): string
    {
        if ($this->status === 'major_repair') return 'red';
        if ($this->status === 'minor_repair') return 'orange';
        if ($this->status === 'retired') return 'red';

        $mileageRemaining = $this->next_service_mileage - $this->current_mileage;
        if ($mileageRemaining < 1000) return 'yellow';

        if ($this->next_maintenance_date) {
            $daysUntilMaintenance = Carbon::now()->diffInDays($this->next_maintenance_date, false);
            if ($daysUntilMaintenance <= 7 && $daysUntilMaintenance >= 0) return 'yellow';
            if ($daysUntilMaintenance < 0) return 'orange';
        }

        return 'green';
    }

    public function getHealthLabelAttribute(): string
    {
        return match ($this->health_status) {
            'green' => 'พร้อมใช้งาน',
            'yellow' => 'ใกล้ครบรอบซ่อม',
            'orange' => 'ซ่อมเล็กน้อย (Minor)',
            'red' => 'ซ่อมหนัก/หยุดวิ่ง (Major)',
        };
    }

    public function getHealthIconAttribute(): string
    {
        return match ($this->health_status) {
            'green' => '🟢',
            'yellow' => '🟡',
            'orange' => '🟠',
            'red' => '🔴',
        };
    }

    public function getVehicleTypeThaiAttribute(): string
    {
        return match ($this->vehicle_type) {
            'bus' => 'รถบัส',
            'van' => 'รถตู้',
            'minibus' => 'รถมินิบัส',
        };
    }

    public function isAvailable(): bool
    {
        return in_array($this->health_status, ['green', 'yellow']);
    }
}
