<?php

namespace App\Modules\ContentManagement\Contracts;

use Illuminate\Database\Eloquent\Collection;

interface ContentServiceInterface
{
    /**
     * Get all protocols.
     *
     * @return Collection
     */
    public function getProtocols(): Collection;

    /**
     * Get a protocol by ID.
     *
     * @param int $id
     * @return mixed
     */
    public function getProtocolDetails(int $id): mixed;

    /**
     * Get all episodes.
     *
     * @return Collection
     */
    public function getEpisodes(): Collection;

    /**
     * Get an episode by ID.
     *
     * @param int $id
     * @return mixed
     */
    public function getEpisodeDetails(int $id): mixed;

    /**
     * Get summaries for an episode.
     *
     * @param int $episodeId
     * @return Collection
     */
    public function getSummariesForEpisode(int $episodeId): Collection;
}
