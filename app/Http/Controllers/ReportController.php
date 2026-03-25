<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\DailyPlan;
use App\Models\MaintenanceLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', '7');
        $days = (int) $period;
        $startDate = Carbon::today()->subDays($days);
        $endDate = Carbon::today();

        // Trip stats
        $plans = DailyPlan::with('vehicle')
            ->whereBetween('plan_date', [$startDate, $endDate])
            ->get();

        $totalTrips = $plans->count();
        $completedTrips = $plans->where('status', 'completed')->count();
        $cancelledTrips = $plans->where('status', 'cancelled')->count();
        $completionRate = $totalTrips > 0 ? round($completedTrips / $totalTrips * 100, 1) : 0;

        // Vehicle utilization
        $vehicles = Vehicle::where('status', '!=', 'retired')->get();
        $vehicleUtilization = [];
        foreach ($vehicles as $vehicle) {
            $tripCount = $plans->where('vehicle_id', $vehicle->id)->count();
            $utilization = $days > 0 ? round($tripCount / $days * 100, 1) : 0;
            $vehicleUtilization[] = [
                'vehicle' => $vehicle,
                'trips' => $tripCount,
                'utilization' => min($utilization, 100),
            ];
        }
        usort($vehicleUtilization, fn($a, $b) => $b['trips'] <=> $a['trips']);

        // Maintenance costs
        $maintenanceLogs = MaintenanceLog::with('vehicle')
            ->whereBetween('service_date', [$startDate, $endDate])
            ->get();
        $totalMaintenanceCost = $maintenanceLogs->sum('cost');
        $scheduledCost = $maintenanceLogs->where('maintenance_type', 'scheduled')->sum('cost');
        $repairCost = $maintenanceLogs->whereIn('maintenance_type', ['minor_repair', 'major_repair'])->sum('cost');

        // Frequent problem vehicles
        $problemVehicles = $maintenanceLogs
            ->whereIn('maintenance_type', ['minor_repair', 'major_repair'])
            ->groupBy('vehicle_id')
            ->map(fn($logs) => [
                'vehicle' => $logs->first()->vehicle,
                'count' => $logs->count(),
                'total_cost' => $logs->sum('cost'),
            ])
            ->sortByDesc('count')
            ->take(5)
            ->values();

        // Chart data - daily trips
        $chartLabels = [];
        $chartTrips = [];
        $chartCompleted = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $dayPlans = $plans->filter(fn($p) => $p->plan_date->format('Y-m-d') === $date->format('Y-m-d'));
            $chartLabels[] = $date->format('d/m');
            $chartTrips[] = $dayPlans->count();
            $chartCompleted[] = $dayPlans->where('status', 'completed')->count();
        }

        return view('reports', compact(
            'period', 'days', 'startDate', 'endDate',
            'totalTrips', 'completedTrips', 'cancelledTrips', 'completionRate',
            'vehicleUtilization', 'totalMaintenanceCost', 'scheduledCost', 'repairCost',
            'problemVehicles', 'chartLabels', 'chartTrips', 'chartCompleted'
        ));
    }
}
