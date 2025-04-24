<?php

namespace Tests\Feature\Authentication;

use App\Modules\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\Feature\ApiTestCase;

class PasswordResetTest extends ApiTestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_validates_forgot_password_request()
    {
        $this->postJson('/api/v1/forgot-password', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);

        $this->postJson('/api/v1/forgot-password', [
            'email' => 'nonexistent@example.com',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_sends_password_reset_link_on_valid_email()
    {
        Notification::fake();

        $user = User::factory()->create();

        $this->postJson('/api/v1/forgot-password', [
            'email' => $user->email,
        ])->assertStatus(200)
          ->assertJson(['message' => 'Password reset link sent successfully.']);

        // TODO: Assert that the correct notification was sent to the user
        // Notification::assertSentTo($user, ResetPasswordNotification::class);
    }

    /** @test */
    public function it_validates_reset_password_request()
    {
        $this->postJson('/api/v1/reset-password', [])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['token', 'email', 'password']);

        $this->postJson('/api/v1/reset-password', [
            'token' => 'invalid-token',
            'email' => 'test@example.com',
            'password' => 'password',
            'password_confirmation' => 'wrong-confirmation',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['token', 'password']);

        $this->postJson('/api/v1/reset-password', [
            'token' => 'valid-token', // Assuming a valid token format for validation test
            'email' => 'invalid-email',
            'password' => 'password',
            'password_confirmation' => 'password',
        ])
            ->assertStatus(422)
            ->assertJsonValidationErrors(['email']);
    }

    /** @test */
    public function it_resets_password_with_valid_token()
    {
        $user = User::factory()->create();
        $token = 'valid-reset-token'; // Replace with actual token generation logic if needed

        // Assuming a mechanism to create a password reset token for the user
        // For testing purposes, we might need to manually create a token entry in the password_reset_tokens table
        \DB::table('password_reset_tokens')->insert([
            'email' => $user->email,
            'token' => Hash::make($token), // Tokens are usually hashed
            'created_at' => now(),
        ]);

        $this->postJson('/api/v1/reset-password', [
            'token' => $token,
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertStatus(200)
          ->assertJson(['message' => 'Your password has been reset.']);

        // Assert that the user's password has been updated
        $this->assertTrue(Hash::check('new-password', $user->fresh()->password));

        // Assert that the reset token has been deleted
        $this->assertDatabaseMissing('password_reset_tokens', [
            'email' => $user->email,
        ]);
    }

    /** @test */
    public function it_returns_error_with_invalid_reset_token()
    {
        $user = User::factory()->create();

        $this->postJson('/api/v1/reset-password', [
            'token' => 'invalid-token',
            'email' => $user->email,
            'password' => 'new-password',
            'password_confirmation' => 'new-password',
        ])->assertStatus(401) // Or 422 depending on implementation
          ->assertJson(['email' => ['This password reset token is invalid.']]); // Or similar validation error message
    }
}
