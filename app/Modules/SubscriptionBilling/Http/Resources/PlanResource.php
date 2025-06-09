<?php

namespace App\Modules\SubscriptionBilling\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use OpenApi\Annotations as OA;

/**
 * @OA\Schema(
 *     schema="PlanResource",
 *     @OA\Property(property="id", type="integer", example=1),
 *     @OA\Property(property="name", type="string", example="Premium Monthly"),
 *     @OA\Property(property="slug", type="string", example="premium-monthly"),
 *     @OA\Property(property="description", type="string", example="Full access to premium features"),
 *     @OA\Property(property="price", type="number", format="float", example=9.99),
 *     @OA\Property(property="interval", type="string", enum={"day", "week", "month", "year"}, example="month"),
 *     @OA\Property(property="interval_count", type="integer", example=1),
 *     @OA\Property(property="trial_period_days", type="integer", nullable=true, example=7),
 *     @OA\Property(property="is_active", type="boolean", example=true)
 * )
 */
class PlanResource extends JsonResource
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
            'name' => $this->name,
            'slug' => $this->slug,
            'description' => $this->description,
            'price' => $this->price,
            'interval' => $this->interval,
            'interval_count' => $this->interval_count,
            'trial_period_days' => $this->trial_period_days,
            'is_active' => $this->is_active,
        ];
    }
}
