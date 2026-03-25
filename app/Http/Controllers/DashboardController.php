<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\DailyPlan;
use App\Models\MaintenanceLog;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $vehicles = Vehicle::all();
        $today = Carbon::today();

        // Summary counts
        $totalVehicles = $vehicles->count();
        $activeCount = $vehicles->where('status', 'active')->count();
        $minorCount = $vehicles->where('status', 'minor_repair')->count();
        $majorCount = $vehicles->where('status', 'major_repair')->count();
        $retiredCount = $vehicles->where('status', 'retired')->count();

        // Today's plans
        $todayPlans = DailyPlan::with('vehicle')->where('plan_date', $today)->get();
        $todayTrips = $todayPlans->count();
        $inProgress = $todayPlans->where('status', 'in_progress')->count();
        $completed = $todayPlans->where('status', 'completed')->count();

        // Alerts - vehicles near maintenance
        $alerts = [];
        foreach ($vehicles as $vehicle) {
            if ($vehicle->status === 'retired') continue;

            $mileageRemaining = $vehicle->next_service_mileage - $vehicle->current_mileage;
            if ($mileageRemaining < 1000 && $mileageRemaining > 0) {
                $alerts[] = [
                    'type' => 'mileage',
                    'level' => 'warning',
                    'vehicle' => $vehicle,
                    'message' => "เหลืออีก {$mileageRemaining} km จะถึงกำหนดเช็คระยะ",
                ];
            }

            if ($vehicle->next_maintenance_date) {
                $daysUntil = Carbon::now()->diffInDays($vehicle->next_maintenance_date, false);
                if ($daysUntil <= 7 && $daysUntil >= 0) {
                    $alerts[] = [
                        'type' => 'schedule',
                        'level' => 'warning',
                        'vehicle' => $vehicle,
                        'message' => "อีก {$daysUntil} วัน ถึงกำหนดซ่อมบำรุง",
                    ];
                } elseif ($daysUntil < 0) {
                    $alerts[] = [
                        'type' => 'schedule',
                        'level' => 'danger',
                        'vehicle' => $vehicle,
                        'message' => "เลยกำหนดซ่อมบำรุงมาแล้ว " . abs($daysUntil) . " วัน",
                    ];
                }
            }

            if ($vehicle->status === 'minor_repair') {
                $alerts[] = [
                    'type' => 'repair',
                    'level' => 'warning',
                    'vehicle' => $vehicle,
                    'message' => "ซ่อมเล็กน้อย: {$vehicle->repair_note}",
                ];
            }
            if ($vehicle->status === 'major_repair') {
                $alerts[] = [
                    'type' => 'repair',
                    'level' => 'danger',
                    'vehicle' => $vehicle,
                    'message' => "ซ่อมหนัก: {$vehicle->repair_note}",
                ];
            }
        }

        // Chart data - last 7 days trips
        $chartLabels = [];
        $chartData = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $chartLabels[] = $date->format('d/m');
            $chartData[] = DailyPlan::where('plan_date', $date)->count();
        }

        return view('dashboard', compact(
            'totalVehicles', 'activeCount', 'minorCount', 'majorCount', 'retiredCount',
            'todayTrips', 'inProgress', 'completed',
            'alerts', 'chartLabels', 'chartData', 'todayPlans'
        ));
    }
}
