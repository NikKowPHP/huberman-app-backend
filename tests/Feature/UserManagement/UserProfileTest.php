<?php

namespace Tests\Feature\UserManagement;

use App\Modules\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Feature\ApiTestCase;

class UserProfileTest extends ApiTestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_fetches_authenticated_user_profile()
    {
        $user = User::factory()->create();
        $token = $user->createToken('auth_token')->plainTextToken;

        $response = $this->getJson('/api/v1/user/profile', [
            'Authorization' => "Bearer $token",
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'id',
                     'name',
                     'email',
                     // Add other expected user fields
                 ]);

        $this->assertEquals($user->id, $response->json('id'));
        $this->assertEquals($user->name, $response->json('name'));
        $this->assertEquals($user->email, $response->json('email'));
    }

    /** @test */
    public function it_returns_unauthorized_for_unauthenticated_user_profile_access()
    {
        $this->getJson('/api/v1/user/profile')
             ->assertStatus(401);
    }
}
