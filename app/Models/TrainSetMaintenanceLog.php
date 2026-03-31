<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TrainSetMaintenanceLog extends Model
{
    protected $fillable = [
        'train_set_id',
        'maintenance_type',
        'description',
        'cost',
        'mileage_at_service',
        'service_date',
        'completed_date',
        'status',
    ];

    protected $casts = [
        'service_date' => 'date',
        'completed_date' => 'date',
        'cost' => 'decimal:2',
    ];

    public function trainSet(): BelongsTo
    {
        return $this->belongsTo(TrainSet::class);
    }

    public function getTypeThaiAttribute(): string
    {
        return match ($this->maintenance_type) {
            'scheduled' => 'ตามกำหนด',
            'minor_repair' => 'ซ่อมเล็กน้อย',
            'major_repair' => 'ซ่อมใหญ่',
            default => 'บันทึกซ่อม',
        };
    }
}