<?php

namespace Tests\Unit;

use App\Modules\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserDeviceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_store_and_retrieve_device_tokens_for_a_user()
    {
        // Create a user
        $user = User::factory()->create();

        // Create a device token
        $deviceToken = 'test_device_token';
        $platform = 'ios';

        // Store the device token for the user
        $user->devices()->create([
            'device_token' => $deviceToken,
            'platform' => $platform,
        ]);

        // Retrieve the device token for the user
        $retrievedDevice = $user->devices()->first();

        // Assert that the retrieved device token is the same as the stored device token
        $this->assertEquals($deviceToken, $retrievedDevice->device_token);
        $this->assertEquals($platform, $retrievedDevice->platform);
    }
}
