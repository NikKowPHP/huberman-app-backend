<?php

namespace Tests\Feature\Policies;

use App\Modules\ProtocolEngine\Models\UserReminder;
use App\Modules\ProtocolEngine\Policies\ReminderPolicy; // Assuming this will be the location
use App\Modules\SubscriptionBilling\Contracts\SubscriptionServiceInterface;
use App\Modules\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Mockery\MockInterface;
use Tests\TestCase;

class ReminderPolicyTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;

    protected User $otherUser;

    protected UserReminder $userReminder;

    protected ReminderPolicy $policy;

    protected MockInterface $subscriptionServiceMock;

    protected function setUp(): void
    {
        parent::setUp();

        $this->user = User::factory()->create();
        $this->otherUser = User::factory()->create();
        $this->userReminder = UserReminder::factory()->forUser($this->user)->create();

        $this->policy = new ReminderPolicy();

        // Mock the SubscriptionService
        $this->subscriptionServiceMock = Mockery::mock(SubscriptionServiceInterface::class);
        $this->instance(SubscriptionServiceInterface::class, $this->subscriptionServiceMock);
    }

    protected function tearDown(): void
    {
        Mockery::close();
        parent::tearDown();
    }

    // --- Test Cases ---

    /** @test */
    public function view_any_is_allowed_for_any_authenticated_user(): void
    {
        // Assuming any logged-in user can attempt to view their reminders list
        $this->assertTrue($this->policy->viewAny($this->user));
        $this->assertTrue($this->policy->viewAny($this->otherUser));
    }

    /** @test */
    public function view_is_allowed_for_owner(): void
    {
        $this->assertTrue($this->policy->view($this->user, $this->userReminder));
    }

    /** @test */
    public function view_is_denied_for_non_owner(): void
    {
        $this->assertFalse($this->policy->view($this->otherUser, $this->userReminder));
    }

    /** @test */
    public function create_is_allowed_for_premium_user(): void
    {
        $this->subscriptionServiceMock
            ->shouldReceive('isUserPremium')
            ->once()
            ->with($this->user)
            ->andReturn(true);

        $this->assertTrue($this->policy->create($this->user));
    }

    /** @test */
    public function create_is_denied_for_non_premium_user(): void
    {
        $this->subscriptionServiceMock
            ->shouldReceive('isUserPremium')
            ->once()
            ->with($this->user)
            ->andReturn(false);

        $this->assertFalse($this->policy->create($this->user));
    }

    /** @test */
    public function update_is_allowed_for_owner(): void
    {
        $this->assertTrue($this->policy->update($this->user, $this->userReminder));
    }

    /** @test */
    public function update_is_denied_for_non_owner(): void
    {
        $this->assertFalse($this->policy->update($this->otherUser, $this->userReminder));
    }

    /** @test */
    public function delete_is_allowed_for_owner(): void
    {
        $this->assertTrue($this->policy->delete($this->user, $this->userReminder));
    }

    /** @test */
    public function delete_is_denied_for_non_owner(): void
    {
        $this->assertFalse($this->policy->delete($this->otherUser, $this->userReminder));
    }

    // Note: restore and forceDelete tests are omitted as the model doesn't use SoftDeletes based on the migration.
}
