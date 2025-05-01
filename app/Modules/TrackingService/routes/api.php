<?php

use Illuminate\Support\Facades\Route;
use App\Modules\TrackingService\Http\Controllers\TrackingController;

// All routes within this file are automatically prefixed with '/api/v1/tracking'
// and should already have the 'api' middleware applied by the RouteServiceProvider.
// Auth middleware is applied within the controller constructor.

Route::post('/log', [TrackingController::class, 'store'])->name('tracking.log');
Route::get('/summary/{protocolId}', [TrackingController::class, 'getSummary'])->name('tracking.summary');

// Add routes for show, update, destroy if implementing those actions:
// Route::get('/log/{logId}', [TrackingController::class, 'show'])->name('tracking.show');
// Route::put('/log/{logId}', [TrackingController::class, 'update'])->name('tracking.update');
// Route::delete('/log/{logId}', [TrackingController::class, 'destroy'])->name('tracking.destroy');