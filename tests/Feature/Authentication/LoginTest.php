<?php

namespace Tests\Feature\Authentication;

use App\Modules\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\ApiTestCase;

class LoginTest extends ApiTestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_validates_login_request()
    {
        $this->postJson('/api/v1/login', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email', 'password']);

        $this->postJson('/api/v1/login', [
            'email' => 'invalid-email',
            'password' => 'password',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_allows_user_to_login_with_correct_credentials()
    {
        $user = User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
                 ->assertJsonStructure([
                     'user' => [
                         'id',
                         'name',
                         'email',
                         // Add other expected user fields
                     ],
                     'token',
                 ]);

        $this->assertAuthenticatedAs($user);
    }

    /** @test */
    public function it_returns_error_with_incorrect_credentials()
    {
        User::factory()->create([
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
        ]);

        $this->postJson('/api/v1/login', [
            'email' => 'test@example.com',
            'password' => 'wrong-password',
        ])->assertStatus(401)
          ->assertJson([
              'message' => 'Invalid credentials',
          ]);

        $this->postJson('/api/v1/login', [
            'email' => 'wrong@example.com',
            'password' => 'password',
        ])->assertStatus(401)
          ->assertJson([
              'message' => 'Invalid credentials',
          ]);
    }
}
