<?php

namespace Tests\Unit;

use App\Modules\ContentManagement\Models\Protocol;
use App\Modules\ContentManagement\Models\Episode;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProtocolTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_has_attributes()
    {
        $protocol = Protocol::factory()->create([
            'name' => 'Test Protocol',
            'description' => 'This is a test protocol.',
        ]);

        $this->assertEquals('Test Protocol', $protocol->name);
        $this->assertEquals('This is a test protocol.', $protocol->description);
    }

    /** @test */
    public function it_has_episodes_relationship()
    {
        $protocol = Protocol::factory()->create();
        $episode = Episode::factory()->create();

        $protocol->episodes()->attach($episode);

        $this->assertInstanceOf(Episode::class, $protocol->episodes->first());
    }

    /** @test */
    public function it_is_fillable()
    {
        $protocol = Protocol::create([
            'name' => 'Test Protocol',
            'description' => 'This is a test protocol.',
        ]);

        $this->assertEquals('Test Protocol', $protocol->name);
        $this->assertEquals('This is a test protocol.', $protocol->description);
    }
}
