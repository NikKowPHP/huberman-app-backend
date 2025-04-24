<?php

namespace Tests\Unit;

use App\Modules\ContentManagement\Models\Episode;
use App\Modules\ContentManagement\Models\Protocol;
use App\Modules\ContentManagement\Models\Summary;
use App\Modules\NotesService\Models\Note;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EpisodeTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_attributes()
    {
        $episode = Episode::factory()->create([
            'title' => 'Test Episode',
            'slug' => 'test-episode',
            'description' => 'This is a test episode.',
            'content' => 'Test content',
            'duration' => 3600,
            'published_at' => now(),
        ]);

        $this->assertEquals('Test Episode', $episode->title);
        $this->assertEquals('test-episode', $episode->slug);
        $this->assertEquals('This is a test episode.', $episode->description);
        $this->assertEquals('Test content', $episode->content);
        $this->assertEquals(3600, $episode->duration);
        $this->assertEquals(now()->format('Y-m-d H:i:s'), $episode->published_at->format('Y-m-d H:i:s'));
    }

    /** @test */
    public function it_has_protocols_relationship()
    {
        $episode = Episode::factory()->create();
        $protocol = Protocol::factory()->create();

        $episode->protocols()->attach($protocol);

        $this->assertInstanceOf(Protocol::class, $episode->protocols->first());
    }

    /** @test */
    public function it_has_summaries_relationship()
    {
        $episode = Episode::factory()->create();
        $summary = Summary::factory()->create(['episode_id' => $episode->id]);

        $this->assertInstanceOf(Summary::class, $episode->summaries->first());
    }

    /** @test */
    public function it_has_notes_relationship()
    {
        $episode = Episode::factory()->create();
        $note = Note::factory()->create(['episode_id' => $episode->id]);

        $this->assertInstanceOf(Note::class, $episode->notes->first());
    }

    /** @test */
    public function it_can_have_many_protocols()
    {
        $episode = Episode::factory()->create();
        $protocol1 = Protocol::factory()->create();
        $protocol2 = Protocol::factory()->create();

        $episode->protocols()->attach([$protocol1->id, $protocol2->id]);

        $this->assertCount(2, $episode->protocols);
        $this->assertInstanceOf(Protocol::class, $episode->protocols->first());
    }

    /** @test */
    public function it_is_fillable()
    {
        $episode = Episode::create([
            'title' => 'Test Episode',
            'slug' => 'test-episode',
            'description' => 'This is a test episode.',
            'content' => 'Test content',
            'duration' => 3600,
            'published_at' => now(),
        ]);

        $this->assertEquals('Test Episode', $episode->title);
        $this->assertEquals('test-episode', $episode->slug);
        $this->assertEquals('This is a test episode.', $episode->description);
        $this->assertEquals('Test content', $episode->content);
        $this->assertEquals(3600, $episode->duration);
        $this->assertEquals(now()->format('Y-m-d H:i:s'), $episode->published_at->format('Y-m-d H:i:s'));
    }
}
