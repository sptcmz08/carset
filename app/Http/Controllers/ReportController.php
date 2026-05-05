<?php

namespace App\Http\Controllers;

use App\Models\ServicePlanDay;
use App\Models\TrainSet;
use App\Models\TrainSetMaintenanceLog;
use App\Models\TrainSetOperationCheck;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        $period = $request->get('period', '7');
        $days = max((int) $period, 1);
        $startDate = Carbon::today()->subDays($days - 1)->startOfDay();
        $endDate = Carbon::today()->endOfDay();
        $periodDays = $days;

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

        $trainSetQuery = TrainSet::query();

        if (TrainSet::hasOperationCheckTable()) {
            $trainSetQuery->with('operationChecks');
        }

        $trainSets = $trainSetQuery
            ->orderBy('display_order')
            ->get();

        $dates = collect();
        for ($i = $days - 1; $i >= 0; $i--) {
            $dates->push(Carbon::today()->subDays($i));
        }

        $entriesByDate = $entries
            ->filter(fn ($entry) => $entry->day)
            ->groupBy(fn ($entry) => Carbon::parse($entry->day->service_date)->format('Y-m-d'))
            ->map(fn (Collection $dayEntries) => $dayEntries->keyBy('train_set_id'));

        $statusMatrix = $trainSets->map(function (TrainSet $trainSet) use ($dates, $entriesByDate) {
            return [
                'train_set' => $trainSet,
                'statuses' => $dates->map(function (Carbon $date) use ($trainSet, $entriesByDate) {
                    $entry = $entriesByDate->get($date->format('Y-m-d'))?->get($trainSet->id);
                    $status = $entry?->effective_status ?? $trainSet->health_status;

                    return [
                        'date' => $date,
                        'status' => $status,
                        'label' => $this->statusLabel($status),
                    ];
                })->all(),
            ];
        });

        $maintenanceLogs = TrainSetMaintenanceLog::with('trainSet')
            ->whereBetween('service_date', [$startDate->toDateString(), $endDate->toDateString()])
            ->get()
            ->map(fn (TrainSetMaintenanceLog $log) => [
                'train_set_id' => $log->train_set_id,
                'train_set' => $log->trainSet,
                'date' => $log->service_date,
                'title' => $log->maintenance_type,
                'description' => $log->description,
                'status' => $log->status,
            ]);

        $operationLogs = collect();

        if (TrainSet::hasOperationCheckTable()) {
            $operationLogs = TrainSetOperationCheck::with('trainSet')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->where(function ($query) {
                    $query->where(function ($departmentQuery) {
                        $departmentQuery
                            ->where('category', 'department')
                            ->where(function ($issueQuery) {
                                $issueQuery
                                    ->where('status', 'not_fit')
                                    ->orWhereNotNull('description');
                            });
                    })->orWhere(function ($maintenanceQuery) {
                        $maintenanceQuery
                            ->where('category', 'maintenance')
                            ->whereNotNull('description');
                    });
                })
                ->latest()
                ->get()
                ->map(fn (TrainSetOperationCheck $check) => [
                    'train_set_id' => $check->train_set_id,
                    'train_set' => $check->trainSet,
                    'date' => $check->created_at,
                    'title' => $check->category === 'maintenance'
                        ? 'Maintenance / ' . $check->check_key
                        : $check->check_key . ' / ' . $check->status_label,
                    'description' => $check->description ?: '-',
                    'status' => $check->status,
                ]);
        }

        $allDamageLogs = $maintenanceLogs
            ->concat($operationLogs)
            ->sortByDesc(fn (array $log) => $log['date'])
            ->values();

        $damageHistory = $trainSets->map(fn (TrainSet $trainSet) => [
            'train_set' => $trainSet,
            'logs' => $allDamageLogs
                ->where('train_set_id', $trainSet->id)
                ->values(),
        ]);

        $damageHistoryTotal = $allDamageLogs->count();
        $affectedTrainSets = $allDamageLogs->pluck('train_set_id')->unique()->count();

        return view('reports', compact(
            'period',
            'days',
            'startDate',
            'endDate',
            'periodDays',
            'totalAssignments',
            'availableAssignments',
            'warningAssignments',
            'outOfServiceAssignments',
            'availabilityRate',
            'trainSets',
            'dates',
            'statusMatrix',
            'damageHistory',
            'damageHistoryTotal',
            'affectedTrainSets'
        ));
    }

    private function statusLabel(string $status): string
    {
        return match ($status) {
            'available' => 'Ready',
            'warning' => 'Caution',
            'out_of_service' => 'Not Service',
            default => '-',
        };
    }
}
