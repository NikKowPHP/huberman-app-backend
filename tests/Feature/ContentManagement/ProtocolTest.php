<?php

namespace Tests\Feature\ContentManagement;

use App\Modules\ContentManagement\Models\Protocol;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ApiTestCase;

class ProtocolTest extends ApiTestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_list_protocols()
    {
        Protocol::factory()->count(3)->create();

        $response = $this->getJson('/api/v1/protocols');

        $response->assertStatus(200)
            ->assertJsonCount(3, 'data')
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'name',
                        'description',
                        'episodes',
                    ],
                ],
            ]);
    }

    /** @test */
    public function it_can_show_a_protocol()
    {
        $protocol = Protocol::factory()->create();

        $response = $this->getJson('/api/v1/protocols/' . $protocol->id);

        $response->assertStatus(200)
            ->assertJsonStructure([
                'id',
                'name',
                'description',
                'episodes',
            ]);
    }
}
