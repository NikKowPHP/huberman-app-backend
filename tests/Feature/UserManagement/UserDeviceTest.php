<?php

namespace Tests\Feature\UserManagement;

use App\Modules\UserManagement\Models\User;
use App\Modules\UserManagement\Models\UserDevice;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDeviceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_stores_a_new_device_token_for_user()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/v1/user/device-token', [
            'device_token' => 'test_device_token',
            'platform' => 'ios'
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Device token updated successfully']);

        $this->assertDatabaseHas('user_devices', [
            'user_id' => $user->id,
            'device_token' => 'test_device_token',
            'platform' => 'ios'
        ]);
    }

    /** @test */
    public function it_updates_existing_device_token_for_user()
    {
        $user = User::factory()->create();
        $user->devices()->create([
            'device_token' => 'old_token',
            'platform' => 'android'
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/v1/user/device-token', [
            'device_token' => 'new_token',
            'platform' => 'ios'
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('user_devices', [
            'user_id' => $user->id,
            'device_token' => 'new_token',
            'platform' => 'ios'
        ]);

        $this->assertDatabaseMissing('user_devices', [
            'device_token' => 'old_token'
        ]);
    }

    /** @test */
    public function it_requires_device_token_and_platform()
    {
        $user = User::factory()->create();
        $this->actingAs($user, 'sanctum');

        $response = $this->postJson('/api/v1/user/device-token', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['device_token', 'platform']);
    }

    /** @test */
    public function it_can_retrieve_user_devices()
    {
        $user = User::factory()->create();
        $device = $user->devices()->create([
            'device_token' => 'test_token',
            'platform' => 'ios'
        ]);

        $this->actingAs($user, 'sanctum');

        $response = $this->getJson('/api/v1/user/devices');

        $response->assertStatus(200)
            ->assertJsonFragment([
                'device_token' => 'test_token',
                'platform' => 'ios'
            ]);
    }
}