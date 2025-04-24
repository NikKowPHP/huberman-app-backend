<?php

use App\Modules\UserManagement\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user/profile', [UserController::class, 'profile']);
});
