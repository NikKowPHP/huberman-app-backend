<?php

use App\Http\Controllers\NoteController;
use Illuminate\Support\Facades\Route;

Route::post('/notes/{note}/categories', [NoteController::class, 'attachCategory'])
    ->middleware('auth:api');
