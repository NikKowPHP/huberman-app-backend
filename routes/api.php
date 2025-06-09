<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\OfflineDataController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/offline-data', [OfflineDataController::class, 'fetchData']);
    Route::post('/offline-data/sync', [OfflineDataController::class, 'syncData']);
});

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])
    ->name('password.email');

Route::post('/reset-password', [NewPasswordController::class, 'reset'])
    ->name('password.reset');

// Premium routes requiring active subscription
Route::middleware(['auth:sanctum', 'premium'])->group(function () {
    // Reminders endpoints
    Route::prefix('reminders')->group(function () {
        Route::get('/', [ReminderController::class, 'index']);
        Route::post('/', [ReminderController::class, 'store']);
        Route::put('/{reminder}', [ReminderController::class, 'update']);
        Route::delete('/{reminder}', [ReminderController::class, 'destroy']);
    });

    // Tracking endpoints
    Route::prefix('tracking')->group(function () {
        Route::post('/log', [TrackingController::class, 'logAdherence']);
        Route::get('/summary', [TrackingController::class, 'getSummary']);
    });
});
