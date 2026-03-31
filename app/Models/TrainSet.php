<?php

namespace App\Models;

use Carbon\Carbon;
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
        'current_mileage',
        'next_service_mileage',
        'last_maintenance_date',
        'next_maintenance_date',
        'maintenance_status',
        'repair_note',
    ];

    protected $casts = [
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
    ];

    public function servicePlanEntries(): HasMany
    {
        return $this->hasMany(ServicePlanEntry::class);
    }

    public function maintenanceLogs(): HasMany
    {
        return $this->hasMany(TrainSetMaintenanceLog::class)->orderByDesc('service_date');
    }

    public function mileageLogs(): HasMany
    {
        return $this->hasMany(TrainSetMileageLog::class)->orderByDesc('log_date');
    }

    public function getMileageRemainingAttribute(): int
    {
        return (int) $this->next_service_mileage - (int) $this->current_mileage;
    }

    public function getDaysUntilMaintenanceAttribute(): ?int
    {
        if (! $this->next_maintenance_date) {
            return null;
        }

        return Carbon::today()->diffInDays($this->next_maintenance_date, false);
    }

    public function getHealthStatusAttribute(): string
    {
        if (in_array($this->maintenance_status, ['major_repair', 'retired'], true)) {
            return 'out_of_service';
        }

        if ($this->mileage_remaining < 0) {
            return 'out_of_service';
        }

        if (($this->days_until_maintenance ?? 1) < 0) {
            return 'out_of_service';
        }

        if ($this->maintenance_status === 'minor_repair') {
            return 'warning';
        }

        if ($this->mileage_remaining <= 1000) {
            return 'warning';
        }

        if (($this->days_until_maintenance ?? 999) <= 7) {
            return 'warning';
        }

        return 'available';
    }

    public function getHealthLabelAttribute(): string
    {
        return match ($this->health_status) {
            'available' => 'พร้อมให้บริการ',
            'warning' => 'ใกล้วาระซ่อม',
            'out_of_service' => 'งดให้บริการ',
            default => 'ไม่ระบุ',
        };
    }

    public function getHealthIconAttribute(): string
    {
        return match ($this->health_status) {
            'available' => '🟢',
            'warning' => '🟡',
            'out_of_service' => '🔴',
            default => '⚪',
        };
    }

    public function getHealthBadgeClassAttribute(): string
    {
        return match ($this->health_status) {
            'available' => 'green',
            'warning' => 'yellow',
            'out_of_service' => 'red',
            default => 'blue',
        };
    }

    public function getMaintenanceStatusLabelAttribute(): string
    {
        return match ($this->maintenance_status) {
            'active' => 'พร้อมใช้งาน',
            'minor_repair' => 'Minor',
            'major_repair' => 'Major',
            'retired' => 'ปลดระวาง',
            default => 'ไม่ระบุ',
        };
    }

    public function getConsistLabelAttribute(): string
    {
        return $this->default_consist_type === '4' ? '4 Cars' : '6 Cars';
    }

    public function getIsPlannedForServiceAttribute(): bool
    {
        return ! empty($this->default_departure_plan_time)
            || ! empty($this->default_outbound_run_no)
            || ! empty($this->default_ktw_platform);
    }
}