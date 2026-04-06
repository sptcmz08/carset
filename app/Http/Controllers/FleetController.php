<?php

namespace App\Http\Controllers;

use App\Models\TrainSet;
use App\Models\TrainSetMaintenanceLog;
use App\Models\TrainSetMileageLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FleetController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $allTrainSets = TrainSet::withCount(['servicePlanEntries', 'maintenanceLogs'])
            ->orderBy('display_order')
            ->get();

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

        return view('fleet', compact('trainSets', 'filter', 'stats'));
    }

    public function show(TrainSet $trainSet)
    {
        $trainSet->load([
            'maintenanceLogs' => fn ($query) => $query->orderBy('service_date', 'desc'),
            'mileageLogs' => fn ($query) => $query->orderBy('log_date', 'desc')->limit(30),
            'servicePlanEntries.day',
        ]);

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

    public function updateStatus(Request $request, TrainSet $trainSet)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,retired',
            'minor_fault_count' => 'nullable|integer|min:0',
            'major_fault_count' => 'nullable|integer|min:0',
            'overhaul_required' => 'nullable|boolean',
            'repair_note' => 'nullable|string',
        ]);

        $trainSet->update([
            'maintenance_status' => $validated['status'],
            'minor_fault_count' => $validated['minor_fault_count'] ?? 0,
            'major_fault_count' => $validated['major_fault_count'] ?? 0,
            'overhaul_required' => (bool) ($validated['overhaul_required'] ?? false),
            'repair_note' => $validated['repair_note'] ?? null,
        ]);

        if (
            $trainSet->minor_fault_count > 0
            || $trainSet->major_fault_count > 0
            || $trainSet->overhaul_required
            || ! empty($validated['repair_note'])
        ) {
            TrainSetMaintenanceLog::create([
                'train_set_id' => $trainSet->id,
                'maintenance_type' => $trainSet->major_fault_count > 0 || $trainSet->overhaul_required ? 'major_repair' : 'minor_repair',
                'description' => $this->buildConditionDescription($trainSet, $validated['repair_note'] ?? null),
                'mileage_at_service' => $trainSet->current_mileage,
                'service_date' => Carbon::today(),
                'status' => 'pending',
            ]);
        }

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
}
