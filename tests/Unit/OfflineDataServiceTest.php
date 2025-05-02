<?php

namespace Tests\Unit;

use App\Models\User;
use App\OfflineData;
use App\Services\OfflineDataService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineDataServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_getDataForUser_returns_collection_of_offline_data()
    {
        $user = User::factory()->create();
        OfflineData::factory()->count(3)->create(['user_id' => $user->id]);
        $offlineDataService = new OfflineDataService();

        $data = $offlineDataService->getDataForUser($user);

        $this->assertCount(3, $data);
        $this->assertInstanceOf('Illuminate\Support\Collection', $data);
    }

    public function test_syncDataForUser_creates_or_updates_offline_data()
    {
        $user = User::factory()->create();
        $offlineDataService = new OfflineDataService();

        $data = [
            ['key' => 'test_key_1', 'value' => 'test_value_1'],
            ['key' => 'test_key_2', 'value' => 'test_value_2'],
        ];

        $offlineDataService->syncDataForUser($user, $data);

        $this->assertDatabaseCount('offline_data', 2);
        $this->assertDatabaseHas('offline_data', ['user_id' => $user->id, 'key' => 'test_key_1', 'value' => 'test_value_1']);

        $data = [
            ['key' => 'test_key_1', 'value' => 'updated_value_1'],
        ];

        $offlineDataService->syncDataForUser($user, $data);

        $this->assertDatabaseCount('offline_data', 2);
        $this->assertDatabaseHas('offline_data', ['user_id' => $user->id, 'key' => 'test_key_1', 'value' => 'updated_value_1']);
    }
}
