<?php

Route::get('/protocols', [\App\Modules\ContentManagement\Http\Controllers\ProtocolController::class, 'index']);
Route::get('/protocols/{id}', [\App\Modules\ContentManagement\Http\Controllers\ProtocolController::class, 'show']);

Route::get('/episodes', [\App\Modules\ContentManagement\Http\Controllers\EpisodeController::class, 'index']);
Route::get('/episodes/{id}', [\App\Modules\ContentManagement\Http\Controllers\EpisodeController::class, 'show']);

Route::get('/summaries', [\App\Modules\ContentManagement\Http\Controllers\SummaryController::class, 'index']);
Route::get('/summaries/{id}', [\App\Modules\ContentManagement\Http\Controllers\SummaryController::class, 'show']);
