<?php

namespace App\Modules\ContentManagement\Services;

use App\Modules\ContentManagement\Contracts\ContentServiceInterface;
use App\Modules\ContentManagement\Models\Episode;
use App\Modules\ContentManagement\Models\Protocol;
use App\Modules\ContentManagement\Models\Summary;
use Illuminate\Database\Eloquent\Collection;

class ContentService implements ContentServiceInterface
{
    /**
     * Get all protocols.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function getProtocols(): \Illuminate\Database\Eloquent\Builder
    {
        return Protocol::query();
    }

    /**
     * Get a protocol by ID.
     *
     * @param int $id
     * @return mixed
     */
    public function getProtocolDetails(int $id): mixed
    {
        return Protocol::findOrFail($id);
    }

    /**
     * Get all episodes.
     *
     * @return Collection
     */
    public function getEpisodes(): Collection
    {
        return Episode::all();
    }

    /**
     * Get an episode by ID.
     *
     * @param int $id
     * @return mixed
     */
    public function getEpisodeDetails(int $id): mixed
    {
        return Episode::findOrFail($id);
    }

    /**
     * Get summaries for an episode.
     *
     * @param int $episodeId
     * @return Collection
     */
    public function getSummariesForEpisode(int $episodeId): Collection
    {
        return Summary::where('episode_id', $episodeId)->get();
    }
}
