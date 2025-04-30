<?php

namespace Tests\Unit\TrackingService;

use App\Modules\TrackingService\Contracts\TrackingServiceInterface;
use App\Modules\TrackingService\Models\TrackingLog;
use App\Modules\TrackingService\Services\TrackingService;
use App\Modules\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Illuminate\Support\Carbon;

class TrackingServiceTest extends TestCase
{
    use RefreshDatabase;

    /**
     * @var TrackingServiceInterface
     */
    protected $trackingService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->trackingService = app(TrackingServiceInterface::class);
    }

    public function test_create_tracking_log(): void
    {
        $user = User::factory()->create();
        $data = ['action' => 'test_action', 'details' => 'test_details'];

        $trackingLog = $this->trackingService->create($user, $data);

        $this->assertInstanceOf(TrackingLog::class, $trackingLog);
        $this->assertEquals($user->id, $trackingLog->user_id);
        $this->assertEquals($data, $trackingLog->data);
    }

    public function test_get_by_id_returns_tracking_log(): void
    {
        $user = User::factory()->create();
        $trackingLog = TrackingLog::factory()->create(['user_id' => $user->id]);

        $retrievedLog = $this->trackingService->getById($user, $trackingLog->id);

        $this->assertInstanceOf(TrackingLog::class, $retrievedLog);
        $this->assertEquals($trackingLog->id, $retrievedLog->id);
    }

    public function test_get_by_id_returns_null_if_not_found(): void
    {
        $user = User::factory()->create();

        $retrievedLog = $this->trackingService->getById($user, 999);

        $this->assertNull($retrievedLog);
    }

    public function test_get_all_returns_collection_of_tracking_logs(): void
    {
        $user = User::factory()->create();
        TrackingLog::factory()->count(3)->create(['user_id' => $user->id]);

        $trackingLogs = $this->trackingService->getAll($user);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $trackingLogs);
        $this->assertCount(3, $trackingLogs);
    }

    public function test_update_tracking_log(): void
    {
        $user = User::factory()->create();
        $trackingLog = TrackingLog::factory()->create(['user_id' => $user->id, 'data' => ['action' => 'old_action']]);
        $newData = ['action' => 'new_action', 'details' => 'new_details'];

        $updatedLog = $this->trackingService->update($user, $trackingLog->id, $newData);

        $this->assertInstanceOf(TrackingLog::class, $updatedLog);
        $this->assertEquals($newData, $updatedLog->data);
    }

    public function test_update_tracking_log_returns_null_if_not_found(): void
    {
        $user = User::factory()->create();
        $newData = ['action' => 'new_action', 'details' => 'new_details'];

        $updatedLog = $this->trackingService->update($user, 999, $newData);

        $this->assertNull($updatedLog);
    }

    public function test_delete_tracking_log(): void
    {
        $user = User::factory()->create();
        $trackingLog = TrackingLog::factory()->create(['user_id' => $user->id]);

        $result = $this->trackingService->delete($user, $trackingLog->id);

        $this->assertTrue($result);
        $this->assertNull(TrackingLog::find($trackingLog->id));
    }

    public function test_delete_tracking_log_returns_false_if_not_found(): void
    {
        $user = User::factory()->create();

        $result = $this->trackingService->delete($user, 999);

        $this->assertFalse($result);
    }

    public function test_get_streak_returns_correct_streak(): void
    {
        $user = User::factory()->create();

        // Create logs for a streak of 3 days
        TrackingLog::factory()->create(['user_id' => $user->id, 'created_at' => Carbon::today()]);
        TrackingLog::factory()->create(['user_id' => $user->id, 'created_at' => Carbon::yesterday()]);
        TrackingLog::factory()->create(['user_id' => $user->id, 'created_at' => Carbon::yesterday()->subDay()]);

        $streak = $this->trackingService->getStreak($user);

        $this->assertEquals(3, $streak);
    }

    public function test_get_streak_returns_0_if_no_logs(): void
    {
        $user = User::factory()->create();

        $streak = $this->trackingService->getStreak($user);

        $this->assertEquals(0, $streak);
    }

    public function test_get_streak_handles_gaps_in_logs(): void
    {
        $user = User::factory()->create();

        // Create logs with a gap
        TrackingLog::factory()->create(['user_id' => $user->id, 'created_at' => Carbon::today()]);
        TrackingLog::factory()->create(['user_id' => $user->id, 'created_at' => Carbon::yesterday()->subDay(2)]);

        $streak = $this->trackingService->getStreak($user);

        $this->assertEquals(1, $streak);
    }

    public function test_get_public_list_returns_public_logs(): void
    {
        $user = User::factory()->create();
        TrackingLog::factory()->create(['user_id' => $user->id, 'is_public' => true]);
        TrackingLog::factory()->create(['user_id' => $user->id, 'is_public' => false]);

        $publicLogs = $this->trackingService->getPublicList($user);

        $this->assertInstanceOf(\Illuminate\Database\Eloquent\Collection::class, $publicLogs);
        $this->assertCount(1, $publicLogs);
    }
}
