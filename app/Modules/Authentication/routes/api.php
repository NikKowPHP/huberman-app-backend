<?php

use Illuminate\Support\Facades\Route;
use App\Modules\Authentication\Http\Controllers\AuthController;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail']);
Route::post('/reset-password', [NewPasswordController::class, 'reset']);
