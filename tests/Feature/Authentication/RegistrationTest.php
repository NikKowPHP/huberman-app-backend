<?php

namespace Tests\Feature\Authentication;

use App\Modules\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\Feature\ApiTestCase; // Use the base ApiTestCase

class RegistrationTest extends ApiTestCase
{
    use RefreshDatabase;

    private string $registerUrl = '/api/v1/register';

    /** @test */
    public function it_requires_name_email_and_password_for_registration(): void
    {
        $this->postJson($this->registerUrl, [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['name', 'email', 'password']);
    }

    /** @test */
    public function it_requires_a_valid_email(): void
    {
        $this->postJson($this->registerUrl, ['email' => 'not-an-email'])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_requires_password_confirmation(): void
    {
        $this->postJson($this->registerUrl, [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            // 'password_confirmation' => 'password', // Missing
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']); // Laravel confirms password by default rule
    }

    /** @test */
    public function it_requires_password_and_confirmation_to_match(): void
    {
        $this->postJson($this->registerUrl, [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'different-password',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

     /** @test */
    public function it_requires_a_minimum_password_length(): void
    {
        // Assuming default Laravel minimum length is 8
        $this->postJson($this->registerUrl, [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'short',
            'password_confirmation' => 'short',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['password']);
    }

    /** @test */
    public function it_requires_email_to_be_unique(): void
    {
        // Arrange: Create an existing user
        User::factory()->create(['email' => 'existing@example.com']);

        // Act & Assert
        $this->postJson($this->registerUrl, [
            'name' => 'Another User',
            'email' => 'existing@example.com', // Use existing email
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_registers_a_user_successfully_and_returns_user_and_token(): void
    {
        $userData = [
            'name' => 'New User',
            'email' => 'newuser@example.com',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ];

        $response = $this->postJson($this->registerUrl, $userData);

        $response->assertStatus(201)
                 ->assertJsonStructure([
                     'message',
                     'data' => [
                         'user' => [
                             'id',
                             'name',
                             'email',
                             'email_verified_at',
                             'created_at',
                             'updated_at',
                         ],
                         'token',
                     ]
                 ])
                 ->assertJsonPath('message', 'User registered successfully.')
                 ->assertJsonPath('data.user.name', $userData['name'])
                 ->assertJsonPath('data.user.email', $userData['email']);

        // Assert user exists in the database
        $this->assertDatabaseHas('users', [
            'name' => $userData['name'],
            'email' => $userData['email'],
        ]);

        // Assert password was hashed
        $user = User::where('email', $userData['email'])->first();
        $this->assertTrue(Hash::check($userData['password'], $user->password));

        // Assert token is valid (basic check: not empty)
        $this->assertNotEmpty($response->json('data.token'));
    }
}
