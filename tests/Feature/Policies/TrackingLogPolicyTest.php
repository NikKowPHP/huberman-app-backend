<?php

namespace Tests\Feature\Policies;

use App\Modules\TrackingService\Models\TrackingLog;
use App\Modules\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingLogPolicyTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that admin users can bypass the premium check.
     */
    public function test_admin_users_can_bypass_premium_check(): void
    {
        $admin = User::factory()->admin()->create();
        $trackingLog = TrackingLog::factory()->create();

        $this->assertTrue($admin->can('view', $trackingLog));
        $this->assertTrue($admin->can('create', TrackingLog::class));
    }

    /**
     * Test that premium users can view and create tracking logs.
     */
    public function test_premium_users_can_view_and_create_tracking_logs(): void
    {
        $premiumUser = User::factory()->premium()->create();
        $trackingLog = TrackingLog::factory()->create();

        $this->assertTrue($premiumUser->can('view', $trackingLog));
        $this->assertTrue($premiumUser->can('create', TrackingLog::class));
    }

    /**
     * Test that free users cannot view and create tracking logs.
     */
    public function test_free_users_cannot_view_and_create_tracking_logs(): void
    {
        $freeUser = User::factory()->create();
        $trackingLog = TrackingLog::factory()->create();

        $this->assertFalse($freeUser->can('view', $trackingLog));
        $this->assertFalse($freeUser->can('create', TrackingLog::class));
    }
}
