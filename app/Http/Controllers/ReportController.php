<?php

namespace App\Http\Controllers;

use App\Models\ServicePlanDay;
use App\Models\TrainSet;
use App\Models\TrainSetMaintenanceLog;
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
        $periodDays = $startDate->diffInDays($endDate) + 1;

        $planDays = ServicePlanDay::query()
            ->whereBetween('service_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->with(['entries.trainSet'])
            ->get();

        $entries = $planDays->flatMap->entries;

        $totalAssignments = $entries->count();
        $availableAssignments = $entries->where('effective_status', 'available')->count();
        $warningAssignments = $entries->where('effective_status', 'warning')->count();
        $outOfServiceAssignments = $entries->where('effective_status', 'out_of_service')->count();
        $availabilityRate = $totalAssignments > 0 ? round($availableAssignments / $totalAssignments * 100, 1) : 0;

        $trainSets = TrainSet::query()->orderBy('display_order')->get();
        $trainSetUtilization = [];
        foreach ($trainSets as $trainSet) {
            $plannedDays = $entries
                ->where('train_set_id', $trainSet->id)
                ->filter(fn ($entry) => $entry->effective_status !== 'out_of_service' && (! empty($entry->departure_plan_time) || ! empty($entry->outbound_run_no)))
                ->count();

            $utilization = $periodDays > 0 ? round($plannedDays / $periodDays * 100, 1) : 0;

            $trainSetUtilization[] = [
                'train_set' => $trainSet,
                'planned_days' => $plannedDays,
                'utilization' => min($utilization, 100),
            ];
        }
        usort($trainSetUtilization, fn ($a, $b) => $b['planned_days'] <=> $a['planned_days']);

        $maintenanceLogs = TrainSetMaintenanceLog::with('trainSet')
            ->whereBetween('service_date', [$startDate, $endDate])
            ->get();
        $totalMaintenanceCost = $maintenanceLogs->sum('cost');
        $scheduledCost = $maintenanceLogs->where('maintenance_type', 'scheduled')->sum('cost');
        $repairCost = $maintenanceLogs->whereIn('maintenance_type', ['minor_repair', 'major_repair'])->sum('cost');

        $problemTrainSets = $maintenanceLogs
            ->whereIn('maintenance_type', ['minor_repair', 'major_repair'])
            ->groupBy('train_set_id')
            ->map(fn ($logs) => [
                'train_set' => $logs->first()->trainSet,
                'count' => $logs->count(),
                'total_cost' => $logs->sum('cost'),
            ])
            ->sortByDesc('count')
            ->take(5)
            ->values();

        $planDaysByDate = $planDays->keyBy(fn (ServicePlanDay $day) => Carbon::parse($day->service_date)->format('Y-m-d'));
        $chartLabels = [];
        $chartAvailable = [];
        $chartWarning = [];
        $chartOut = [];
        for ($i = $days; $i >= 0; $i--) {
            $date = Carbon::today()->subDays($i);
            $day = $planDaysByDate->get($date->format('Y-m-d'));
            $dayEntries = $day?->entries ?? collect();

            $chartLabels[] = $date->format('d/m');
            $chartAvailable[] = $dayEntries->where('effective_status', 'available')->count();
            $chartWarning[] = $dayEntries->where('effective_status', 'warning')->count();
            $chartOut[] = $dayEntries->where('effective_status', 'out_of_service')->count();
        }

        return view('reports', compact(
            'period', 'days', 'startDate', 'endDate', 'periodDays',
            'totalAssignments', 'availableAssignments', 'warningAssignments', 'outOfServiceAssignments', 'availabilityRate',
            'trainSetUtilization', 'totalMaintenanceCost', 'scheduledCost', 'repairCost',
            'problemTrainSets', 'chartLabels', 'chartAvailable', 'chartWarning', 'chartOut'
        ));
    }
}
