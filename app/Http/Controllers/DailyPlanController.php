<?php

namespace App\Http\Controllers;

use App\Models\Vehicle;
use App\Models\DailyPlan;
use Carbon\Carbon;
use Illuminate\Http\Request;

class DailyPlanController extends Controller
{
    public function index(Request $request)
    {
        $date = $request->get('date', Carbon::today()->format('Y-m-d'));
        $plans = DailyPlan::with('vehicle')
            ->where('plan_date', $date)
            ->orderBy('departure_time')
            ->get();

        $vehicles = Vehicle::whereIn('status', ['active', 'minor_repair'])->get();

        return view('daily-plan', compact('plans', 'vehicles', 'date'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'plan_date' => 'required|date',
            'vehicle_id' => 'required|exists:vehicles,id',
            'driver_name' => 'required|string|max:255',
            'route' => 'required|string|max:255',
            'departure_time' => 'required',
            'return_time' => 'nullable',
            'passengers' => 'nullable|integer|min:0',
            'note' => 'nullable|string',
        ]);

        $validated['status'] = 'planned';
        DailyPlan::create($validated);

        return redirect()->route('daily-plan', ['date' => $validated['plan_date']])
            ->with('success', 'เพิ่มแผนงานสำเร็จ');
    }

    public function update(Request $request, DailyPlan $dailyPlan)
    {
        $validated = $request->validate([
            'driver_name' => 'required|string|max:255',
            'route' => 'required|string|max:255',
            'departure_time' => 'required',
            'return_time' => 'nullable',
            'passengers' => 'nullable|integer|min:0',
            'status' => 'required|in:planned,in_progress,completed,cancelled',
            'note' => 'nullable|string',
        ]);

        $dailyPlan->update($validated);

        return redirect()->route('daily-plan', ['date' => $dailyPlan->plan_date->format('Y-m-d')])
            ->with('success', 'อัปเดตแผนงานสำเร็จ');
    }

    public function destroy(DailyPlan $dailyPlan)
    {
        $date = $dailyPlan->plan_date->format('Y-m-d');
        $dailyPlan->delete();

        return redirect()->route('daily-plan', ['date' => $date])
            ->with('success', 'ลบแผนงานสำเร็จ');
    }
}
