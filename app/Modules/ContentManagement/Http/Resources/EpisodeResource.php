<?php

namespace App\Modules\ContentManagement\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class EpisodeResource extends JsonResource
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
            'title' => $this->title,
            'slug' => $this->slug,
            'description' => $this->description,
            'content' => $this->content,
            'duration' => $this->duration,
            'published_at' => $this->published_at,
            'protocols' => ProtocolResource::collection($this->protocols),
            'summaries' => SummaryResource::collection($this->summaries),
        ];
    }
}
