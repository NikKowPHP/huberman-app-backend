<?php

use App\Modules\Authentication\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::post('/forgot-password', [ForgotPasswordController::class, 'sendResetLinkEmail'])->name('password.email');
Route::post('/reset-password', [NewPasswordController::class, 'reset'])->name('password.update');
Route::post('/logout', [AuthController::class, 'logout']);
