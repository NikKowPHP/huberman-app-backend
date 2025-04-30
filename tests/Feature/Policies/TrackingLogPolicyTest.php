<?php

namespace Tests\Feature\Policies;

use App\Modules\TrackingService\Models\TrackingLog;
use App\Modules\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TrackingLogPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_tracking_log_if_premium(): void
    {
        $user = User::factory()->create();
        $user->subscriptions()->create(['plan_id' => 1, 'ends_at' => now()->addDay()]);
        $trackingLog = TrackingLog::factory()->create();

        $this->assertTrue($user->can('view', $trackingLog));
        $this->assertTrue($user->can('viewAny', TrackingLog::class));
        $this->assertTrue($user->can('create', TrackingLog::class));
        $this->assertTrue($user->can('update', $trackingLog));
        $this->assertTrue($user->can('delete', $trackingLog));
        $this->assertTrue($user->can('restore', $trackingLog));
        $this->assertTrue($user->can('forceDelete', $trackingLog));
    }

    public function test_user_cannot_view_tracking_log_if_not_premium(): void
    {
        $user = User::factory()->create();
        $trackingLog = TrackingLog::factory()->create();

        $this->assertFalse($user->can('view', $trackingLog));
        $this->assertFalse($user->can('viewAny', TrackingLog::class));
        $this->assertFalse($user->can('create', TrackingLog::class));
        $this->assertFalse($user->can('update', $trackingLog));
        $this->assertFalse($user->can('delete', $trackingLog));
        $this->assertFalse($user->can('restore', $trackingLog));
        $this->assertFalse($user->can('forceDelete', $trackingLog));
    }
}
