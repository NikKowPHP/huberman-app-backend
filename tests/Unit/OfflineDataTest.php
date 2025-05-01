<?php

namespace Tests\Unit;

use App\OfflineData;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Tests\TestCase;

class OfflineDataTest extends TestCase
{
    use RefreshDatabase, DatabaseMigrations;

    /** @test */
    public function it_can_create_an_offline_data()
    {
        $offlineData = OfflineData::factory()->create();

        $this->assertNotNull($offlineData->id);
    }
}
