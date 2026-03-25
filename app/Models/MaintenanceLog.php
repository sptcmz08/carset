<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MaintenanceLog extends Model
{
    protected $fillable = [
        'vehicle_id', 'maintenance_type', 'description', 'cost',
        'mileage_at_service', 'service_date', 'completed_date', 'status',
    ];

    protected $casts = [
        'service_date' => 'date',
        'completed_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function vehicle()
    {
        return $this->belongsTo(Vehicle::class);
    }

    public function getTypeThaiAttribute(): string
    {
        return match ($this->maintenance_type) {
            'scheduled' => 'ตามกำหนด',
            'minor_repair' => 'ซ่อมเล็กน้อย',
            'major_repair' => 'ซ่อมใหญ่',
        };
    }
}
