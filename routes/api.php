<?php

use App\Http\Controllers\PostController;
use App\Http\Controllers\RoutineController;
use Illuminate\Support\Facades\Route;

Route::apiResource('posts', PostController::class)->only([
    'index', 'show', 'store'
]);

Route::post('posts/{post}/comments', [PostController::class, 'storeComment']);

Route::apiResource('routines', RoutineController::class);
