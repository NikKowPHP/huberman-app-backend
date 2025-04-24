<?php

namespace App\Modules\SubscriptionBilling\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SubscriptionBilling\Http\Resources\PlanResource;
use App\Modules\SubscriptionBilling\Http\Resources\SubscriptionResource;
use App\Modules\SubscriptionBilling\Models\Plan;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    public function plans()
    {
        $plans = Plan::all();

        return PlanResource::collection($plans);
    }

    public function userSubscription(Request $request)
    {
        $subscription = $request->user()->subscriptions()->active()->first();

        return new SubscriptionResource($subscription);
    }
}
