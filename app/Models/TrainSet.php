<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Schema;

class TrainSet extends Model
{
    private const DAILY_MAINTENANCE_WINDOWS = [
        ['07:50', '14:00'],
        ['08:00', '14:10'],
        ['08:05', '11:00'],
        ['14:05', '21:00'],
        ['14:15', '21:00'],
    ];

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
        'planning_note',
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

    public function operationChecks(): HasMany
    {
        return $this->hasMany(TrainSetOperationCheck::class)->latest();
    }

    public function operationCheckSnapshot(): array
    {
        $latest = $this->latestOperationChecks();

        $departments = [];
        foreach (TrainSetOperationCheck::DEPARTMENTS as $key => $label) {
            $check = $latest->get('department:' . $key);
            $departments[$key] = [
                'label' => $label,
                'status' => $check?->status ?? 'fit',
                'description' => $check?->description ?? '',
            ];
        }

        $maintenance = [];
        foreach (TrainSetOperationCheck::MAINTENANCE_TYPES as $key => $label) {
            $check = $latest->get('maintenance:' . $key);
            $maintenance[$key] = [
                'key' => $key,
                'label' => $label,
                'status' => $check?->status ?? 'fit',
                'description' => $check?->description ?? '',
            ];
        }

        return [
            'departments' => $departments,
            'maintenance' => $maintenance,
        ];
    }

    public function latestOperationChecks()
    {
        if (! self::hasOperationCheckTable()) {
            return collect();
        }

        $checks = $this->relationLoaded('operationChecks')
            ? $this->operationChecks
            : $this->operationChecks()->get();

        return $checks
            ->sortByDesc('created_at')
            ->unique(fn (TrainSetOperationCheck $check) => $check->category . ':' . $check->check_key)
            ->keyBy(fn (TrainSetOperationCheck $check) => $check->category . ':' . $check->check_key);
    }

    public static function hasOperationCheckTable(): bool
    {
        try {
            return Schema::hasTable('train_set_operation_checks');
        } catch (\Throwable) {
            return false;
        }
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
        if ($this->has_operation_not_fit || $this->has_active_maintenance_window) {
            return 'out_of_service';
        }

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

    public function getHasOperationNotFitAttribute(): bool
    {
        $latest = $this->latestOperationChecks();

        foreach (TrainSetOperationCheck::DEPARTMENTS as $key => $label) {
            if ($latest->get('department:' . $key)?->status === 'not_fit') {
                return true;
            }
        }

        foreach (TrainSetOperationCheck::MAINTENANCE_TYPES as $key => $label) {
            if ($latest->get('maintenance:' . $key)?->status === 'not_fit') {
                return true;
            }
        }

        return false;
    }

    public function getHasActiveMaintenanceWindowAttribute(): bool
    {
        $latest = $this->latestOperationChecks();
        $latestFitAt = $latest
            ->filter(fn (TrainSetOperationCheck $check) => $check->category === 'department' && $check->status === 'fit')
            ->max('created_at');

        foreach (TrainSetOperationCheck::MAINTENANCE_TYPES as $key => $label) {
            $check = $latest->get('maintenance:' . $key);

            if (! $check || empty($check->description)) {
                continue;
            }

            if ($latestFitAt && $latestFitAt->greaterThan($check->created_at)) {
                continue;
            }

            if ($key === 'Daily') {
                if ($this->dailyMaintenanceWindowIsActive($check->description)) {
                    return true;
                }

                continue;
            }

            if ($this->scheduledMaintenanceWindowIsActive($key, $check->created_at)) {
                return true;
            }
        }

        return false;
    }

    private function dailyMaintenanceWindowIsActive(?string $description): bool
    {
        if (! $description) {
            return false;
        }

        $normalized = preg_replace('/\s+/', '', $description);
        $now = Carbon::now();

        foreach (self::DAILY_MAINTENANCE_WINDOWS as [$startTime, $endTime]) {
            $windowText = $startTime . '-' . $endTime;

            if (! str_contains($normalized, $windowText)) {
                continue;
            }

            $start = Carbon::today()->setTimeFromTimeString($startTime);
            $end = Carbon::today()->setTimeFromTimeString($endTime);

            if ($now->betweenIncluded($start, $end)) {
                return true;
            }
        }

        return false;
    }

    private function scheduledMaintenanceWindowIsActive(string $key, ?Carbon $startedAt): bool
    {
        if (! $startedAt) {
            return true;
        }

        $end = match ($key) {
            'Weekly' => $startedAt->copy()->addDays(7),
            'Monthly' => $startedAt->copy()->addMonth(),
            'Yearly' => $startedAt->copy()->addYear(),
            'Overhaul' => null,
            default => $startedAt,
        };

        if ($end === null) {
            return true;
        }

        return Carbon::now()->lessThanOrEqualTo($end);
    }

    public function getHealthReasonsAttribute(): array
    {
        $reasons = [];

        if ($this->has_operation_not_fit) {
            $reasons[] = 'Not fit';
        }

        if ($this->has_active_maintenance_window) {
            $reasons[] = 'Maintenance';
        }

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
            'available' => 'Ready',
            'warning' => 'Caution',
            'out_of_service' => 'Not Service',
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
