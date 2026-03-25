<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DailyPlanController;
use App\Http\Controllers\FleetController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/daily-plan', [DailyPlanController::class, 'index'])->name('daily-plan');
Route::post('/daily-plan', [DailyPlanController::class, 'store'])->name('daily-plan.store');
Route::put('/daily-plan/{dailyPlan}', [DailyPlanController::class, 'update'])->name('daily-plan.update');
Route::delete('/daily-plan/{dailyPlan}', [DailyPlanController::class, 'destroy'])->name('daily-plan.destroy');

Route::get('/fleet', [FleetController::class, 'index'])->name('fleet');
Route::get('/fleet/{vehicle}', [FleetController::class, 'show'])->name('fleet.show');
Route::post('/fleet/{vehicle}/mileage', [FleetController::class, 'updateMileage'])->name('fleet.mileage');
Route::post('/fleet/{vehicle}/status', [FleetController::class, 'updateStatus'])->name('fleet.status');

Route::get('/reports', [ReportController::class, 'index'])->name('reports');
