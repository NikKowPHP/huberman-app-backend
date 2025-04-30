<?php

namespace App\Modules\ProtocolEngine\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin \App\Modules\ProtocolEngine\Models\UserReminder
 */
class ReminderResource extends JsonResource
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
            'user_id' => $this->user_id, // Keep for reference, maybe remove later
            'protocol_id' => $this->protocol_id,
            'reminder_time' => $this->reminder_time,
            'frequency' => $this->frequency,
            'specific_days' => $this->specific_days, // Already cast to array by model
            'message' => $this->message,
            'is_active' => $this->is_active,
            'last_sent_at' => $this->last_sent_at?->toISOString(), // Include if needed
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
