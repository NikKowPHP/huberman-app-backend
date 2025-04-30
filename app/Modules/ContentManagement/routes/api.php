<?php

use App\Modules\ContentManagement\Http\Controllers\EpisodeController;
use Illuminate\Support\Facades\Route;

Route::get('/episodes/{episode}/public-notes', [EpisodeController::class, 'publicNotes']);
