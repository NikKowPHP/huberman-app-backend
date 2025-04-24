<?php

use Illuminate\Support\Facades\Route;

Route::group(['middleware' => ['auth:sanctum', \App\Http\Middleware\CheckPremiumAccess::class]], function () {
    // Place your routes here that require authentication
});
