<?php

namespace App\Modules\SubscriptionBilling\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

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
