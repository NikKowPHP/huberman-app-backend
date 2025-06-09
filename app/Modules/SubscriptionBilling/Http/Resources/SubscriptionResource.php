<?php

namespace App\Modules\SubscriptionBilling\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="SubscriptionResource",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="user_id", type="integer", example=123),
 *     @OA\Property(property="plan_id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="default"),
 *     @OA\Property(property="stripe_id", type="string", example="sub_1P8Z2xKZv8"),
 *     @OA\Property(property="stripe_status", type="string", enum={"active", "past_due", "unpaid", "canceled", "incomplete", "incomplete_expired", "trialing"}, example="active"),
 *     @OA\Property(property="stripe_price", type="string", example="price_1P8Z2xKZv8"),
 *     @OA\Property(property="quantity", type="integer", example=1),
 *     @OA\Property(property="trial_ends_at", type="string", format="date-time", nullable=true, example="2025-07-01T00:00:00Z"),
 *     @OA\Property(property="ends_at", type="string", format="date-time", nullable=true, example="2025-08-01T00:00:00Z")
 * )
 */
class SubscriptionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'plan_id' => $this->plan_id,
            'name' => $this->name,
            'stripe_id' => $this->stripe_id,
            'stripe_status' => $this->stripe_status,
            'stripe_price' => $this->stripe_price,
            'quantity' => $this->quantity,
            'trial_ends_at' => $this->trial_ends_at,
            'ends_at' => $this->ends_at,
        ];
    }
}
