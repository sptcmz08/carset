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

            if ($trainSet->mileage_remaining < 0) {
                $alerts[] = [
                    'type' => 'mileage',
                    'level' => 'danger',
                    'trainSet' => $trainSet,
                    'message' => 'เกินกำหนดเช็คระยะแล้ว ' . abs($trainSet->mileage_remaining) . ' km',
                ];
            } elseif ($trainSet->mileage_remaining <= 1000) {
                $alerts[] = [
                    'type' => 'mileage',
                    'level' => 'warning',
                    'trainSet' => $trainSet,
                    'message' => 'เหลืออีก ' . $trainSet->mileage_remaining . ' km จะถึงกำหนดเช็คระยะ',
                ];
            }

            if ($trainSet->days_until_maintenance !== null) {
                if ($trainSet->days_until_maintenance < 0) {
                    $alerts[] = [
                        'type' => 'schedule',
                        'level' => 'danger',
                        'trainSet' => $trainSet,
                        'message' => 'เลยกำหนดซ่อมบำรุงมาแล้ว ' . abs($trainSet->days_until_maintenance) . ' วัน',
                    ];
                } elseif ($trainSet->days_until_maintenance <= 7) {
                    $alerts[] = [
                        'type' => 'schedule',
                        'level' => 'warning',
                        'trainSet' => $trainSet,
                        'message' => 'อีก ' . $trainSet->days_until_maintenance . ' วัน ถึงกำหนดซ่อมบำรุง',
                    ];
                }
            }

            if ($trainSet->maintenance_status === 'minor_repair') {
                $alerts[] = [
                    'type' => 'repair',
                    'level' => 'warning',
                    'trainSet' => $trainSet,
                    'message' => 'Minor: ' . ($trainSet->repair_note ?: 'มีรายการต้องตรวจสอบก่อนใช้งาน'),
                ];
            }

            if ($trainSet->maintenance_status === 'major_repair') {
                $alerts[] = [
                    'type' => 'repair',
                    'level' => 'danger',
                    'trainSet' => $trainSet,
                    'message' => 'Major: ' . ($trainSet->repair_note ?: 'งดให้บริการจนกว่าจะซ่อมเสร็จ'),
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
