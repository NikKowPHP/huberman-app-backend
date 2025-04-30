<?php

use App\Modules\ProtocolEngine\Http\Controllers\ReminderController;
use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum', \App\Http\Middleware\CheckPremiumAccess::class]], function () {
    // Place your routes here that require authentication

    // Reminder Routes (Premium Access Required for Create)
    Route::post('/reminders', [ReminderController::class, 'store'])->can('create', App\Modules\ProtocolEngine\Models\UserReminder::class);
});
