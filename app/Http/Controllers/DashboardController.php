<?php

namespace App\Http\Controllers;

use App\Models\ServicePlanDay;
use App\Models\TrainSet;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index()
    {
        $trainSets = TrainSet::query()->orderBy('display_order')->get();
        $today = Carbon::today();

        $totalTrainSets = $trainSets->count();
        $availableCount = $trainSets->where('health_status', 'available')->count();
        $warningCount = $trainSets->where('health_status', 'warning')->count();
        $outOfServiceCount = $trainSets->where('health_status', 'out_of_service')->count();

        $todayDay = ServicePlanDay::query()
            ->whereDate('service_date', $today->toDateString())
            ->with(['entries.trainSet'])
            ->first();

        $todayEntries = $todayDay?->entries ?? collect();
        $todayPlanned = $todayEntries->where('effective_status', 'available')->count();
        $todayWarning = $todayEntries->where('effective_status', 'warning')->count();
        $todayOut = $todayEntries->where('effective_status', 'out_of_service')->count();

        $alerts = [];
        foreach ($trainSets as $trainSet) {
            if ($trainSet->maintenance_status === 'retired') {
                continue;
            }

            if ($trainSet->requires_mileage_inspection) {
                $alerts[] = [
                    'type' => 'mileage',
                    'level' => 'warning',
                    'trainSet' => $trainSet,
                    'message' => 'ถึงระยะไมล์ตรวจเช็กทุก 5,000 km แล้ว',
                ];
            }

            if ($trainSet->major_fault_count >= 1 || $trainSet->overhaul_required || $trainSet->minor_fault_count > 3) {
                $messages = [];

                if ($trainSet->overhaul_required) {
                    $messages[] = 'Overhaul';
                }

                if ($trainSet->major_fault_count >= 1) {
                    $messages[] = 'Fault Major ' . $trainSet->major_fault_count . ' รายการ';
                }

                if ($trainSet->minor_fault_count > 3) {
                    $messages[] = 'Fault Minor เกิน 3 รายการ';
                }

                $alerts[] = [
                    'type' => 'repair',
                    'level' => 'danger',
                    'trainSet' => $trainSet,
                    'message' => implode(' / ', $messages),
                ];
            } elseif ($trainSet->minor_fault_count > 0) {
                $alerts[] = [
                    'type' => 'repair',
                    'level' => 'warning',
                    'trainSet' => $trainSet,
                    'message' => 'Fault Minor ' . $trainSet->minor_fault_count . ' รายการ',
                ];
            }
        }

        $recentDays = ServicePlanDay::query()
            ->whereBetween('service_date', [$today->copy()->subDays(6)->toDateString(), $today->toDateString()])
            ->with('entries.trainSet')
            ->get()
            ->keyBy(fn (ServicePlanDay $day) => Carbon::parse($day->service_date)->format('Y-m-d'));

        $chartLabels = [];
        $chartAvailable = [];
        $chartWarning = [];
        $chartOut = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $day = $recentDays->get($date->format('Y-m-d'));
            $entries = $day?->entries ?? collect();

            $chartLabels[] = $date->format('d/m');
            $chartAvailable[] = $entries->where('effective_status', 'available')->count();
            $chartWarning[] = $entries->where('effective_status', 'warning')->count();
            $chartOut[] = $entries->where('effective_status', 'out_of_service')->count();
        }

        return view('dashboard', compact(
            'totalTrainSets', 'availableCount', 'warningCount', 'outOfServiceCount',
            'todayPlanned', 'todayWarning', 'todayOut',
            'alerts', 'chartLabels', 'chartAvailable', 'chartWarning', 'chartOut', 'todayEntries', 'trainSets'
        ));
    }
}
