<?php

namespace App\Modules\ContentManagement\Http\Resources;

use App\Modules\SubscriptionBilling\Contracts\SubscriptionServiceInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ProtocolResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $subscriptionService = app(SubscriptionServiceInterface::class);
        $user = $request->user();

        $data = [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'episodes' => EpisodeResource::collection($this->episodes),
        ];

        if ($user) {
            if ($subscriptionService->userHasActivePremiumSubscription($user)) {
                $data['implementation_guide'] = $this->implementation_guide;
                $data['is_free'] = $this->is_free;
            } else {
                $data['is_free'] = $this->is_free;
            }
        }

        return $data;
    }
}
