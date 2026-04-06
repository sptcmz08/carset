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
        'minor_fault_count',
        'major_fault_count',
        'overhaul_required',
        'repair_note',
    ];

    protected $casts = [
        'last_maintenance_date' => 'date',
        'next_maintenance_date' => 'date',
        'overhaul_required' => 'boolean',
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

    public function getRequiresMileageInspectionAttribute(): bool
    {
        $currentMileage = (int) $this->current_mileage;

        return $currentMileage > 0 && $currentMileage % 5000 === 0;
    }

    public function getHasMinorFaultWarningAttribute(): bool
    {
        $minorFaultCount = (int) $this->minor_fault_count;

        return $minorFaultCount > 0 && $minorFaultCount <= 3;
    }

    public function getHasCriticalFaultConditionAttribute(): bool
    {
        return (bool) $this->overhaul_required
            || (int) $this->major_fault_count >= 1
            || (int) $this->minor_fault_count > 3;
    }

    public function getHealthStatusAttribute(): string
    {
        if (in_array($this->maintenance_status, ['major_repair', 'retired'], true)) {
            return 'out_of_service';
        }

        if ($this->has_critical_fault_condition) {
            return 'out_of_service';
        }

        if ($this->maintenance_status === 'minor_repair') {
            return 'warning';
        }

        if ($this->requires_mileage_inspection || $this->has_minor_fault_warning) {
            return 'warning';
        }

        return 'available';
    }

    public function getHealthReasonsAttribute(): array
    {
        $reasons = [];

        if ($this->maintenance_status === 'retired') {
            $reasons[] = 'ปลดระวาง';
        }

        if ($this->maintenance_status === 'major_repair') {
            $reasons[] = 'อยู่ระหว่างซ่อมใหญ่';
        }

        if ($this->overhaul_required) {
            $reasons[] = 'มีงาน Overhaul';
        }

        if ((int) $this->major_fault_count >= 1) {
            $reasons[] = 'Fault Major ' . (int) $this->major_fault_count . ' รายการ';
        }

        if ((int) $this->minor_fault_count > 3) {
            $reasons[] = 'Fault Minor เกิน 3 รายการ';
        } elseif ($this->has_minor_fault_warning) {
            $reasons[] = 'Fault Minor ' . (int) $this->minor_fault_count . ' รายการ';
        }

        if ($this->maintenance_status === 'minor_repair') {
            $reasons[] = 'มีรายการซ่อมเล็กน้อย';
        }

        if ($this->requires_mileage_inspection) {
            $reasons[] = 'ถึงระยะไมล์ทุก 5,000 km';
        }

        return $reasons;
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
