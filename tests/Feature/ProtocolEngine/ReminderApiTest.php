<?php

namespace Tests\Feature\ProtocolEngine;

use App\Modules\ProtocolEngine\Models\UserReminder;
use App\Modules\SubscriptionBilling\Contracts\SubscriptionServiceInterface;
use App\Modules\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Mockery;
use Mockery\MockInterface;
use Tests\Feature\ApiTestCase; // Use the base API test case

class ReminderApiTest extends ApiTestCase
{
    use RefreshDatabase;

    protected User $premiumUser;

    protected User $freeUser;

    protected MockInterface $subscriptionServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->premiumUser = User::factory()->create();
        $this->freeUser = User::factory()->create();

        // Mock the SubscriptionService
        $this->subscriptionServiceMock = Mockery::mock(SubscriptionServiceInterface::class);
        $this->instance(SubscriptionServiceInterface::class, $this->subscriptionServiceMock);

        // Default mock behavior (can be overridden in tests)
        $this->subscriptionServiceMock->shouldReceive('isUserPremium')
            ->with($this->premiumUser)->andReturn(true);
        $this->subscriptionServiceMock->shouldReceive('isUserPremium')
            ->with($this->freeUser)->andReturn(false);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // --- CREATE Tests ---

    /** @test */
    public function unauthenticated_user_cannot_create_reminder(): void
    {
        $this->postJson('/api/v1/reminders', [])
            ->assertUnauthorized(); // 401
    }

    /** @test */
    public function free_user_cannot_create_reminder(): void
    {
        $this->actingAs($this->freeUser, 'sanctum');

        $reminderData = UserReminder::factory()->make()->toArray();

        $this->postJson('/api/v1/reminders', $reminderData)
            ->assertForbidden(); // 403 (due to policy/middleware)
    }

    /** @test */
    public function premium_user_can_create_daily_reminder_with_valid_data(): void
    {
        $this->actingAs($this->premiumUser, 'sanctum');

        $reminderData = [
            'reminder_time' => '09:00:00',
            'frequency' => 'daily',
            'message' => 'Daily check-in',
            'is_active' => true,
            // protocol_id is optional
        ];

        $response = $this->postJson('/api/v1/reminders', $reminderData);

        $response->assertCreated() // 201
            ->assertJsonStructure([
                'data' => [
                    'id',
                    'reminder_time',
                    'frequency',
                    'specific_days',
                    'message',
                    'is_active',
                    'protocol_id',
                    'created_at',
                    'updated_at',
                ],
            ])
            ->assertJsonFragment([
                'reminder_time' => '09:00:00',
                'frequency' => 'daily',
                'message' => 'Daily check-in',
                'is_active' => true,
                'specific_days' => null, // Ensure specific_days is null for daily
            ]);

        $this->assertDatabaseHas('user_reminders', [
            'user_id' => $this->premiumUser->id,
            'reminder_time' => '09:00:00',
            'frequency' => 'daily',
            'message' => 'Daily check-in',
            'is_active' => true,
            'specific_days' => null,
        ]);
    }

    /** @test */
    public function premium_user_can_create_specific_days_reminder_with_valid_data(): void
    {
        $this->actingAs($this->premiumUser, 'sanctum');

        $reminderData = [
            'reminder_time' => '14:30:00',
            'frequency' => 'specific_days',
            'specific_days' => ['Mon', 'Wed', 'Fri'],
            'message' => 'Midweek reminder',
            'is_active' => true,
        ];

        $response = $this->postJson('/api/v1/reminders', $reminderData);

        $response->assertCreated()
            ->assertJsonFragment([
                'frequency' => 'specific_days',
                'specific_days' => ['Mon', 'Wed', 'Fri'],
            ]);

        // Check DB stores JSON correctly
        $reminder = UserReminder::where('user_id', $this->premiumUser->id)->first();
        $this->assertNotNull($reminder);
        $this->assertEquals(['Mon', 'Wed', 'Fri'], $reminder->specific_days);
        $this->assertEquals('14:30:00', $reminder->reminder_time);
    }

    /** @test */
    public function create_reminder_fails_with_invalid_data(): void
    {
        $this->actingAs($this->premiumUser, 'sanctum');

        $invalidData = [
            'reminder_time' => 'invalid-time', // Invalid format
            'frequency' => 'sometimes', // Invalid frequency
            'specific_days' => 'Monday', // Not an array
            'message' => '', // Missing message
            'is_active' => 'yes', // Not boolean
        ];

        $this->postJson('/api/v1/reminders', $invalidData)
            ->assertStatus(422) // Unprocessable Entity
            ->assertJsonValidationErrors(['reminder_time', 'frequency', 'specific_days', 'message', 'is_active']);
    }

    /** @test */
    public function create_reminder_fails_if_specific_days_is_missing_when_frequency_is_specific_days(): void
    {
        $this->actingAs($this->premiumUser, 'sanctum');

        $data = [
            'reminder_time' => '10:00:00',
            'frequency' => 'specific_days',
            // specific_days is missing
            'message' => 'Test message',
        ];

        $this->postJson('/api/v1/reminders', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['specific_days']);
    }

    /** @test */
    public function create_reminder_fails_if_specific_days_is_provided_when_frequency_is_not_specific_days(): void
    {
        $this->actingAs($this->premiumUser, 'sanctum');

        $data = [
            'reminder_time' => '10:00:00',
            'frequency' => 'daily',
            'specific_days' => ['Mon', 'Tue'], // Should not be here for daily
            'message' => 'Test message',
        ];

        $this->postJson('/api/v1/reminders', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['specific_days']);
    }

     /** @test */
    public function create_reminder_fails_if_specific_days_contains_invalid_values(): void
    {
        $this->actingAs($this->premiumUser, 'sanctum');

        $data = [
            'reminder_time' => '10:00:00',
            'frequency' => 'specific_days',
            'specific_days' => ['Mon', 'InvalidDay', 'Fri'], // Contains invalid value
            'message' => 'Test message',
        ];

        $this->postJson('/api/v1/reminders', $data)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['specific_days.1']); // Error on the invalid index
    }

    // --- LIST Tests ---

    /** @test */
    public function unauthenticated_user_cannot_list_reminders(): void
    {
        $this->getJson('/api/v1/reminders')
            ->assertUnauthorized();
    }

    /** @test */
    public function free_user_can_list_own_reminders(): void
    {
        $this->actingAs($this->freeUser, 'sanctum');
        $reminder = UserReminder::factory()->forUser($this->freeUser)->create();

        $response = $this->getJson('/api/v1/reminders');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'reminder_time',
                        'frequency',
                        'specific_days',
                        'message',
                        'is_active',
                        'protocol_id',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'id' => $reminder->id,
                'message' => $reminder->message,
            ]);
    }

    /** @test */
    public function premium_user_can_list_own_reminders(): void
    {
        $this->actingAs($this->premiumUser, 'sanctum');
        $reminder = UserReminder::factory()->forUser($this->premiumUser)->create();

        $response = $this->getJson('/api/v1/reminders');

        $response->assertOk()
            ->assertJsonStructure([
                'data' => [
                    '*' => [
                        'id',
                        'reminder_time',
                        'frequency',
                        'specific_days',
                        'message',
                        'is_active',
                        'protocol_id',
                        'created_at',
                        'updated_at',
                    ],
                ],
            ])
            ->assertJsonCount(1, 'data')
            ->assertJsonFragment([
                'id' => $reminder->id,
                'message' => $reminder->message,
            ]);
    }

    /** @test */
    public function free_user_cannot_list_other_users_reminders(): void
    {
        $this->actingAs($this->freeUser, 'sanctum');
        $otherUserReminder = UserReminder::factory()->create(); // Created without a specific user

        $response = $this->getJson('/api/v1/reminders');

        $response->assertOk()
            ->assertJsonCount(0, 'data'); // Should not see other user's reminders
    }

    /** @test */
    public function premium_user_cannot_list_other_users_reminders(): void
    {
        $this->actingAs($this->premiumUser, 'sanctum');
        $otherUserReminder = UserReminder::factory()->create(); // Created without a specific user

        $response = $this->getJson('/api/v1/reminders');

        $response->assertOk()
            ->assertJsonCount(0, 'data'); // Should not see other user's reminders
    }

    // --- UPDATE Tests ---

    /** @test */
    public function unauthenticated_user_cannot_update_reminder(): void
    {
        $reminder = UserReminder::factory()->create();
        $this->putJson("/api/v1/reminders/{$reminder->id}", [])
            ->assertUnauthorized();
    }

    /** @test */
    public function free_user_cannot_update_reminder(): void
    {
        $this->actingAs($this->freeUser, 'sanctum');
        $reminder = UserReminder::factory()->forUser($this->freeUser)->create();
        $updateData = ['message' => 'Updated message'];

        $this->putJson("/api/v1/reminders/{$reminder->id}", $updateData)
            ->assertForbidden();
    }

    /** @test */
    public function premium_user_can_update_own_reminder_with_valid_data(): void
    {
        $this->actingAs($this->premiumUser, 'sanctum');
        $reminder = UserReminder::factory()->forUser($this->premiumUser)->create();
        $updateData = ['message' => 'Updated message'];

        $response = $this->putJson("/api/v1/reminders/{$reminder->id}", $updateData);

        $response->assertOk()
            ->assertJsonFragment(['message' => 'Updated message']);

        $this->assertDatabaseHas('user_reminders', [
            'id' => $reminder->id,
            'message' => 'Updated message',
        ]);
    }

    /** @test */
    public function premium_user_cannot_update_other_users_reminder(): void
    {
        $this->actingAs($this->premiumUser, 'sanctum');
        $otherUserReminder = UserReminder::factory()->create();
        $updateData = ['message' => 'Updated message'];

        $this->putJson("/api/v1/reminders/{$otherUserReminder->id}", $updateData)
            ->assertForbidden();
    }

    /** @test */
    public function update_reminder_fails_with_invalid_data(): void
    {
        $this->actingAs($this->premiumUser, 'sanctum');
        $reminder = UserReminder::factory()->forUser($this->premiumUser)->create();
        $invalidData = ['reminder_time' => 'invalid-time'];

        $this->putJson("/api/v1/reminders/{$reminder->id}", $invalidData)
            ->assertStatus(422)
            ->assertJsonValidationErrors(['reminder_time']);
    }

    // --- DELETE Tests ---

    /** @test */
    public function unauthenticated_user_cannot_delete_reminder(): void
    {
        $reminder = UserReminder::factory()->create();
        $this->deleteJson("/api/v1/reminders/{$reminder->id}")
            ->assertUnauthorized();
    }

    /** @test */
    public function free_user_cannot_delete_reminder(): void
    {
        $this->actingAs($this->freeUser, 'sanctum');
        $reminder = UserReminder::factory()->forUser($this->freeUser)->create();

        $this->deleteJson("/api/v1/reminders/{$reminder->id}")
            ->assertForbidden();
    }

    /** @test */
    public function premium_user_can_delete_own_reminder(): void
    {
        $this->actingAs($this->premiumUser, 'sanctum');
        $reminder = UserReminder::factory()->forUser($this->premiumUser)->create();

        $this->deleteJson("/api/v1/reminders/{$reminder->id}")
            ->assertNoContent(); // 204

        $this->assertDatabaseMissing('user_reminders', ['id' => $reminder->id]);
    }

    /** @test */
    public function premium_user_cannot_delete_other_users_reminder(): void
    {
        $this->actingAs($this->premiumUser, 'sanctum');
        $otherUserReminder = UserReminder::factory()->create();

        $this->deleteJson("/api/v1/reminders/{$otherUserReminder->id}")
            ->assertForbidden();
    }
}
