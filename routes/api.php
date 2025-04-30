<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Modules\TrackingService\Http\Controllers\TrackingController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/webhooks/google', [\App\Modules\SubscriptionBilling\Http\Controllers\WebhookController::class, 'handleGoogleWebhook'])->withoutMiddleware('csrf');

Route::post('/api/v1/tracking/log', [TrackingController::class, 'store'])->middleware('auth:sanctum');
