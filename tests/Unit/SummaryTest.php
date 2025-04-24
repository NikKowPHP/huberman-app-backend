<?php

namespace Tests\Unit;

use App\Modules\ContentManagement\Models\Summary;
use App\Modules\ContentManagement\Models\Episode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SummaryTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_attributes()
    {
        $summary = Summary::factory()->create([
            'episode_id' => Episode::factory()->create()->id,
            'content' => 'This is a test summary.',
        ]);

        $this->assertEquals('This is a test summary.', $summary->content);
    }

    /** @test */
    public function it_has_episode_relationship()
    {
        $summary = Summary::factory()->create();
        $episode = Episode::factory()->create();
        $summary->episode_id = $episode->id;
        $summary->save();

        $this->assertInstanceOf(Episode::class, $summary->episode);
    }

    /** @test */
    public function it_is_fillable()
    {
        $episode = Episode::factory()->create();
        $summary = Summary::create([
            'episode_id' => $episode->id,
            'content' => 'This is a test summary.',
        ]);

        $this->assertEquals('This is a test summary.', $summary->content);
    }
}
