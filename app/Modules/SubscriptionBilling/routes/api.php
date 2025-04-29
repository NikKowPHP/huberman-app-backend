<?php

use App\Modules\SubscriptionBilling\Http\Controllers\SubscriptionController;
use App\Modules\SubscriptionBilling\Http\Controllers\WebhookController;
use App\Http\Middleware\VerifyStripeWebhookSignature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

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

Route::get('/plans', [SubscriptionController::class, 'plans']);

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/webhooks/apple', [\App\Modules\SubscriptionBilling\Http\Controllers\WebhookController::class, 'handleAppleWebhook'])->withoutMiddleware('csrf');

Route::middleware('auth:sanctum')->get('/user/subscription', [SubscriptionController::class, 'userSubscription']);

Route::post('/webhooks/stripe', [WebhookController::class, 'handleStripeWebhook'])->withoutMiddleware('csrf')->middleware(VerifyStripeWebhookSignature::class);
