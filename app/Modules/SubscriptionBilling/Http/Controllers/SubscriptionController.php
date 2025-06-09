<?php

namespace App\Modules\SubscriptionBilling\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Modules\SubscriptionBilling\Http\Resources\PlanResource;
use App\Modules\SubscriptionBilling\Http\Resources\SubscriptionResource;
use App\Modules\SubscriptionBilling\Models\Plan;
use Illuminate\Http\Request;
use OpenApi\Annotations as OA;

class SubscriptionController extends Controller
{
    /**
     * Get list of available subscription plans
     *
     * @OA\Get(
     *     path="/plans",
     *     tags={"SubscriptionBilling"},
     *     summary="Get available plans",
     *     description="Returns list of all available subscription plans",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="List of plans",
     *         @OA\JsonContent(
     *             type="array",
     *             @OA\Items(ref="#/components/schemas/PlanResource")
     *         )
     *     )
     * )
     */
    public function plans()
    {
        $plans = Plan::all();

        return PlanResource::collection($plans);
    }

    /**
     * Get user's active subscription
     *
     * @OA\Get(
     *     path="/user/subscription",
     *     tags={"SubscriptionBilling"},
     *     summary="Get user subscription",
     *     description="Returns the authenticated user's active subscription",
     *     security={{"bearerAuth": {}}},
     *     @OA\Response(
     *         response=200,
     *         description="User's active subscription or null",
     *         @OA\JsonContent(ref="#/components/schemas/SubscriptionResource")
     *     ),
     *     @OA\Response(
     *         response=401,
     *         description="Unauthorized",
     *         @OA\JsonContent(ref="#/components/responses/UnauthorizedError")
     *     )
     * )
     */
    public function userSubscription(Request $request)
    {
        $subscription = $request->user()->subscriptions()->active()->first();

        return new SubscriptionResource($subscription);
    }
}
