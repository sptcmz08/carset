<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DailyPlanController;
use App\Http\Controllers\FleetController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

Route::get('/daily-plan', [DailyPlanController::class, 'index'])->name('daily-plan');
Route::post('/daily-plan', [DailyPlanController::class, 'save'])->name('daily-plan.save');
Route::post('/daily-plan/copy-previous', [DailyPlanController::class, 'copyPreviousDay'])->name('daily-plan.copy-previous');
Route::get('/daily-plan/master', [DailyPlanController::class, 'master'])->name('daily-plan.master');
Route::post('/daily-plan/master', [DailyPlanController::class, 'saveMaster'])->name('daily-plan.master.save');
Route::get('/daily-plan/pdf', [DailyPlanController::class, 'exportPdf'])->name('daily-plan.pdf');

Route::get('/fleet', [FleetController::class, 'index'])->name('fleet');
Route::get('/fleet/{trainSet}', [FleetController::class, 'show'])->name('fleet.show');
Route::post('/fleet/{trainSet}/mileage', [FleetController::class, 'updateMileage'])->name('fleet.mileage');
Route::post('/fleet/{trainSet}/status', [FleetController::class, 'updateStatus'])->name('fleet.status');
Route::post('/fleet/{trainSet}/schedule', [FleetController::class, 'updateSchedule'])->name('fleet.schedule');

Route::get('/reports', [ReportController::class, 'index'])->name('reports');
