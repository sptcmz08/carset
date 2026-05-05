<?php

namespace App\Http\Controllers;

use App\Models\TrainSet;
use App\Models\TrainSetMaintenanceLog;
use App\Models\TrainSetMileageLog;
use App\Models\TrainSetOperationCheck;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FleetController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $trainSetQuery = TrainSet::query();

        if (TrainSet::hasOperationCheckTable()) {
            $trainSetQuery->with(['operationChecks']);
        }

        $allTrainSets = $trainSetQuery
            ->withCount(['servicePlanEntries', 'maintenanceLogs'])
            ->orderBy('display_order')
            ->get();

        $topMileageRanks = $allTrainSets
            ->sortByDesc(fn (TrainSet $set) => (int) $set->current_mileage)
            ->take(3)
            ->values()
            ->mapWithKeys(fn (TrainSet $set, int $idx) => [$set->id => $idx + 1])
            ->all();

        $trainSets = $allTrainSets->filter(function (TrainSet $trainSet) use ($filter) {
            return match ($filter) {
                'active' => $trainSet->health_status === 'available',
                'warning' => $trainSet->health_status === 'warning',
                'out_of_service' => $trainSet->health_status === 'out_of_service',
                'minor_repair', 'major_repair', 'retired' => $trainSet->maintenance_status === $filter,
                default => true,
            };
        })->values();

        $stats = [
            'total' => $allTrainSets->count(),
            'available' => $allTrainSets->where('health_status', 'available')->count(),
            'warning' => $allTrainSets->where('health_status', 'warning')->count(),
            'out_of_service' => $allTrainSets->where('health_status', 'out_of_service')->count(),
            'minor' => $allTrainSets->where('maintenance_status', 'minor_repair')->count(),
            'major' => $allTrainSets->where('maintenance_status', 'major_repair')->count(),
            'retired' => $allTrainSets->where('maintenance_status', 'retired')->count(),
        ];

        return view('fleet', compact('trainSets', 'filter', 'stats', 'topMileageRanks'));
    }

    public function show(TrainSet $trainSet)
    {
        $relations = [
            'maintenanceLogs' => fn ($query) => $query->orderBy('service_date', 'desc'),
            'mileageLogs' => fn ($query) => $query->orderBy('log_date', 'desc')->limit(30),
            'servicePlanEntries.day',
        ];

        if (TrainSet::hasOperationCheckTable()) {
            $relations['operationChecks'] = fn ($query) => $query->latest()->limit(60);
        }

        $trainSet->load($relations);

        return response()->json([
            'train_set' => $trainSet,
            'health_status' => $trainSet->health_status,
            'health_label' => $trainSet->health_label,
            'health_icon' => $trainSet->health_icon,
            'health_badge_class' => $trainSet->health_badge_class,
            'health_reasons' => $trainSet->health_reasons,
            'maintenance_status_label' => $trainSet->maintenance_status_label,
            'maintenance_logs' => $trainSet->maintenanceLogs,
            'mileage_logs' => $trainSet->mileageLogs,
            'operation_checks' => (TrainSet::hasOperationCheckTable() ? $trainSet->operationChecks : collect())->map(fn (TrainSetOperationCheck $check) => [
                'id' => $check->id,
                'category' => $check->category,
                'check_key' => $check->check_key,
                'check_label' => $check->check_label,
                'status' => $check->status,
                'status_label' => $check->status_label,
                'description' => $check->description,
                'created_at' => $check->created_at,
            ])->values(),
        ]);
    }

    public function updateMileage(Request $request, TrainSet $trainSet)
    {
        $validated = $request->validate([
            'mileage' => 'required|integer|min:' . $trainSet->current_mileage,
        ]);

        $trainSet->update(['current_mileage' => $validated['mileage']]);

        TrainSetMileageLog::create([
            'train_set_id' => $trainSet->id,
            'log_date' => Carbon::today(),
            'mileage' => $validated['mileage'],
        ]);

        return response()->json($this->buildStatusResponse($trainSet->fresh()));
    }

    public function updatePlanningNote(Request $request, TrainSet $trainSet)
    {
        $validated = $request->validate([
            'planning_note' => 'nullable|string|max:255',
        ]);

        $trainSet->update([
            'planning_note' => $validated['planning_note'] ?? null,
        ]);

        return response()->json([
            'success' => true,
            'planning_note' => $trainSet->planning_note,
        ]);
    }

    public function updateOperationCheck(Request $request, TrainSet $trainSet)
    {
        if (! TrainSet::hasOperationCheckTable()) {
            return response()->json([
                'success' => false,
                'message' => 'Operation check table has not been migrated yet.',
            ], 503);
        }

        $validated = $request->validate([
            'departments' => 'array',
            'departments.*.status' => 'nullable|in:fit,not_fit',
            'departments.*.description' => 'nullable|string|max:2000',
            'maintenance' => 'array',
            'maintenance.*.status' => 'nullable|in:fit,not_fit',
            'maintenance.*.description' => 'nullable|string|max:2000',
        ]);

        $latest = $trainSet->operationChecks()
            ->get()
            ->sortByDesc('created_at')
            ->unique(fn (TrainSetOperationCheck $check) => $check->category . ':' . $check->check_key)
            ->keyBy(fn (TrainSetOperationCheck $check) => $check->category . ':' . $check->check_key);

        $departmentRows = $validated['departments'] ?? [];
        foreach (TrainSetOperationCheck::DEPARTMENTS as $key => $label) {
            $row = $departmentRows[$key] ?? [];

            $this->createOperationCheckWhenChanged($trainSet, $latest->get('department:' . $key), [
                'category' => 'department',
                'check_key' => $key,
                'status' => $row['status'] ?? 'fit',
                'description' => $this->stringOrNull($row['description'] ?? null),
            ]);
        }

        $maintenanceRows = $validated['maintenance'] ?? [];
        foreach (TrainSetOperationCheck::MAINTENANCE_TYPES as $key => $label) {
            $row = $maintenanceRows[$key] ?? [];
            $status = $row['status'] ?? 'fit';
            $description = $this->stringOrNull($row['description'] ?? null);
            $latestCheck = $latest->get('maintenance:' . $key);

            if ($status !== 'fit' || $description !== null || $latestCheck) {
                $this->createOperationCheckWhenChanged($trainSet, $latestCheck, [
                    'category' => 'maintenance',
                    'check_key' => $key,
                    'status' => $status,
                    'description' => $description,
                ]);
            }
        }

        $fresh = $trainSet->fresh(['operationChecks']);

        if ($fresh->has_operation_not_fit || $fresh->has_active_maintenance_window) {
            $fresh->update(['maintenance_status' => 'major_repair']);
            $fresh = $fresh->fresh(['operationChecks']);
        } elseif ($fresh->maintenance_status !== 'retired') {
            $fresh->update(['maintenance_status' => 'active']);
            $fresh = $fresh->fresh(['operationChecks']);
        }

        return response()->json([
            ...$this->buildStatusResponse($fresh),
            'operation_snapshot' => $fresh->operationCheckSnapshot(),
            'operation_checks' => $this->buildOperationHistory($fresh),
        ]);
    }

    public function updateFault(Request $request, TrainSet $trainSet)
    {
        $validated = $request->validate([
            'minor_fault_count' => 'nullable|integer|min:0',
            'major_fault_count' => 'nullable|integer|min:0',
            'overhaul_required' => 'nullable|boolean',
            'repair_note' => 'nullable|string',
        ]);

        $before = $trainSet->only(['minor_fault_count', 'major_fault_count', 'overhaul_required', 'repair_note']);

        $trainSet->update([
            'minor_fault_count' => $validated['minor_fault_count'] ?? 0,
            'major_fault_count' => $validated['major_fault_count'] ?? 0,
            'overhaul_required' => (bool) ($validated['overhaul_required'] ?? false),
            'repair_note' => $validated['repair_note'] ?? null,
        ]);

        $changed = (
            (int) ($before['minor_fault_count'] ?? 0) !== (int) $trainSet->minor_fault_count
            || (int) ($before['major_fault_count'] ?? 0) !== (int) $trainSet->major_fault_count
            || (bool) ($before['overhaul_required'] ?? false) !== (bool) $trainSet->overhaul_required
            || (string) ($before['repair_note'] ?? '') !== (string) ($trainSet->repair_note ?? '')
        );

        if ($changed && (
            $trainSet->minor_fault_count > 0
            || $trainSet->major_fault_count > 0
            || $trainSet->overhaul_required
            || ! empty($trainSet->repair_note)
        )) {
            TrainSetMaintenanceLog::create([
                'train_set_id' => $trainSet->id,
                'maintenance_type' => $trainSet->major_fault_count > 0 || $trainSet->overhaul_required ? 'major_repair' : 'minor_repair',
                'description' => $this->buildConditionDescription($trainSet, $trainSet->repair_note),
                'mileage_at_service' => $trainSet->current_mileage,
                'service_date' => Carbon::today(),
                'status' => 'pending',
            ]);
        }

        return response()->json($this->buildStatusResponse($trainSet->fresh()));
    }

    public function updateStatus(Request $request, TrainSet $trainSet)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,minor_repair,major_repair,retired',
        ]);

        $trainSet->update(['maintenance_status' => $validated['status']]);

        return response()->json($this->buildStatusResponse($trainSet->fresh()));
    }

    public function updateSchedule(Request $request, TrainSet $trainSet)
    {
        $validated = $request->validate([
            'next_service_mileage' => 'required|integer|min:' . $trainSet->current_mileage,
            'last_maintenance_date' => 'nullable|date',
            'next_maintenance_date' => 'nullable|date',
        ]);

        $trainSet->update($validated);

        if (! empty($validated['last_maintenance_date'])) {
            TrainSetMaintenanceLog::create([
                'train_set_id' => $trainSet->id,
                'maintenance_type' => 'scheduled',
                'description' => 'อัปเดตกำหนดซ่อมบำรุงตามแผน',
                'cost' => 0,
                'mileage_at_service' => $trainSet->current_mileage,
                'service_date' => Carbon::parse($validated['last_maintenance_date']),
                'completed_date' => Carbon::parse($validated['last_maintenance_date']),
                'status' => 'completed',
            ]);
        }

        return response()->json($this->buildStatusResponse($trainSet->fresh()));
    }

    private function buildStatusResponse(TrainSet $trainSet): array
    {
        return [
            'success' => true,
            'health_status' => $trainSet->health_status,
            'health_label' => $trainSet->health_label,
            'health_icon' => $trainSet->health_icon,
            'health_badge_class' => $trainSet->health_badge_class,
            'health_reasons' => $trainSet->health_reasons,
            'maintenance_status' => $trainSet->maintenance_status,
            'maintenance_status_label' => $trainSet->maintenance_status_label,
            'mileage_remaining' => $trainSet->mileage_remaining,
            'days_until_maintenance' => $trainSet->days_until_maintenance,
        ];
    }

    private function createOperationCheckWhenChanged(TrainSet $trainSet, ?TrainSetOperationCheck $latestCheck, array $attributes): void
    {
        if (
            $latestCheck
            && $latestCheck->status === ($attributes['status'] ?? null)
            && $this->stringOrNull($latestCheck->description) === ($attributes['description'] ?? null)
        ) {
            return;
        }

        TrainSetOperationCheck::create([
            'train_set_id' => $trainSet->id,
            ...$attributes,
        ]);
    }

    private function buildOperationHistory(TrainSet $trainSet): array
    {
        return $trainSet->operationChecks()
            ->latest()
            ->limit(60)
            ->get()
            ->map(fn (TrainSetOperationCheck $check) => [
                'id' => $check->id,
                'category' => $check->category,
                'check_key' => $check->check_key,
                'check_label' => $check->check_label,
                'status' => $check->status,
                'status_label' => $check->status_label,
                'description' => $check->description,
                'created_at' => $check->created_at,
            ])
            ->values()
            ->all();
    }

    private function buildConditionDescription(TrainSet $trainSet, ?string $repairNote = null): string
    {
        $segments = [];

        if ($trainSet->overhaul_required) {
            $segments[] = 'Overhaul';
        }

        if ($trainSet->major_fault_count > 0) {
            $segments[] = 'Fault Major ' . $trainSet->major_fault_count . ' รายการ';
        }

        if ($trainSet->minor_fault_count > 0) {
            $segments[] = 'Fault Minor ' . $trainSet->minor_fault_count . ' รายการ';
        }

        if ($repairNote) {
            $segments[] = $repairNote;
        }

        return implode(' / ', $segments ?: ['บันทึกสภาพขบวน']);
    }

    private function stringOrNull(?string $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $trimmed = trim($value);

        return $trimmed === '' ? null : $trimmed;
    }
}
