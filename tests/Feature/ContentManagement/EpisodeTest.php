<?php

namespace Tests\Feature\ContentManagement;

use App\Modules\ContentManagement\Models\Episode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ApiTestCase;

class EpisodeTest extends ApiTestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_episodes()
    {
        Episode::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/episodes');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'title',
                        'slug',
                        'description',
                        'content',
                        'duration',
                        'published_at',
                        'protocols',
                        'summaries',
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_can_show_an_episode()
    {
        $episode = Episode::factory()->create();

        $response = $this->getJson('/api/v1/episodes/' . $episode->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'title',
                'slug',
                'description',
                'content',
                'duration',
                'published_at',
                'protocols',
                'summaries',
            ]);
    }
}
