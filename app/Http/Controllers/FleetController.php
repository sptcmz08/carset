<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\MaintenanceLog;
use App\Models\MileageLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class FleetController extends Controller
{
    public function index(Request $request)
    {
        $filter = $request->get('filter', 'all');
        $query = Vehicle::withCount(['dailyPlans', 'maintenanceLogs']);

        if ($filter !== 'all') {
            if ($filter === 'warning') {
                $query->where(function ($q) {
                    $q->whereRaw('(next_service_mileage - current_mileage) < 1000')
                      ->orWhere('next_maintenance_date', '<=', Carbon::now()->addDays(7));
                })->where('status', 'active');
            } else {
                $query->where('status', $filter);
            }
        }

        $vehicles = $query->get();
        $allVehicles = Vehicle::all();

        $stats = [
            'total' => $allVehicles->count(),
            'active' => $allVehicles->where('status', 'active')->count(),
            'minor' => $allVehicles->where('status', 'minor_repair')->count(),
            'major' => $allVehicles->where('status', 'major_repair')->count(),
            'retired' => $allVehicles->where('status', 'retired')->count(),
        ];

        return view('fleet', compact('vehicles', 'filter', 'stats'));
    }

    public function show(Vehicle $vehicle)
    {
        $vehicle->load(['maintenanceLogs' => function ($q) {
            $q->orderBy('service_date', 'desc');
        }, 'mileageLogs' => function ($q) {
            $q->orderBy('log_date', 'desc')->limit(30);
        }]);

        return response()->json([
            'vehicle' => $vehicle,
            'health_status' => $vehicle->health_status,
            'health_label' => $vehicle->health_label,
            'health_icon' => $vehicle->health_icon,
            'vehicle_type_thai' => $vehicle->vehicle_type_thai,
            'maintenance_logs' => $vehicle->maintenanceLogs,
            'mileage_logs' => $vehicle->mileageLogs,
        ]);
    }

    public function updateMileage(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'mileage' => 'required|integer|min:' . $vehicle->current_mileage,
        ]);

        $vehicle->update(['current_mileage' => $validated['mileage']]);

        MileageLog::create([
            'vehicle_id' => $vehicle->id,
            'log_date' => Carbon::today(),
            'mileage' => $validated['mileage'],
        ]);

        return response()->json([
            'success' => true,
            'health_status' => $vehicle->fresh()->health_status,
            'health_label' => $vehicle->fresh()->health_label,
            'health_icon' => $vehicle->fresh()->health_icon,
        ]);
    }

    public function updateStatus(Request $request, Vehicle $vehicle)
    {
        $validated = $request->validate([
            'status' => 'required|in:active,minor_repair,major_repair,retired',
            'repair_note' => 'nullable|string',
        ]);

        $vehicle->update($validated);

        if (in_array($validated['status'], ['minor_repair', 'major_repair'])) {
            MaintenanceLog::create([
                'vehicle_id' => $vehicle->id,
                'maintenance_type' => $validated['status'] === 'minor_repair' ? 'minor_repair' : 'major_repair',
                'description' => $validated['repair_note'] ?? 'บันทึกอาการเสีย',
                'mileage_at_service' => $vehicle->current_mileage,
                'service_date' => Carbon::today(),
                'status' => 'pending',
            ]);
        }

        return response()->json([
            'success' => true,
            'health_status' => $vehicle->fresh()->health_status,
            'health_label' => $vehicle->fresh()->health_label,
            'health_icon' => $vehicle->fresh()->health_icon,
        ]);
    }
}
