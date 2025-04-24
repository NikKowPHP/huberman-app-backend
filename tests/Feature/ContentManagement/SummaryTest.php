<?php

namespace Tests\Feature\ContentManagement;

use App\Modules\ContentManagement\Models\Summary;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ApiTestCase;

class SummaryTest extends ApiTestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_summaries()
    {
        Summary::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/summaries');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'content',
                        'episode',
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_can_show_a_summary()
    {
        $summary = Summary::factory()->create();

        $response = $this->getJson('/api/v1/summaries/' . $summary->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'content',
                'episode',
            ]);
    }
}
