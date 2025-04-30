<?php

namespace Tests\Unit\TrackingService;

use PHPUnit\Framework\TestCase;
use App\Modules\TrackingService\Models\TrackingLog;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\UserManagement\Models\User;
use App\Modules\ContentManagement\Models\Protocol;

class TrackingLogTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that a TrackingLog can be created.
     */
    public function test_tracking_log_can_be_created(): void
    {
        // Create a user and a protocol
        $user = User::factory()->create();
        $protocol = Protocol::factory()->create();

        // Create a tracking log
        $trackingLog = TrackingLog::create([
            'user_id' => $user->id,
            'protocol_id' => $protocol->id,
            'tracked_at' => '2023-10-27',
            'notes' => 'Completed the protocol as planned.',
            'metadata' => ['duration' => 30, 'intensity' => 'medium'],
        ]);

        // Assert that the tracking log was created
        $this->assertDatabaseHas('user_protocol_tracking', [
            'user_id' => $user->id,
            'protocol_id' => $protocol->id,
            'tracked_at' => '2023-10-27',
            'notes' => 'Completed the protocol as planned.',
            'metadata' => json_encode(['duration' => 30, 'intensity' => 'medium']),
        ]);

        $this->assertInstanceOf(TrackingLog::class, $trackingLog);
        $this->assertEquals($user->id, $trackingLog->user_id);
        $this->assertEquals($protocol->id, $trackingLog->protocol_id);
        $this->assertEquals('2023-10-27', $trackingLog->tracked_at->format('Y-m-d'));
        $this->assertEquals('Completed the protocol as planned.', $trackingLog->notes);
        $this->assertEquals(['duration' => 30, 'intensity' => 'medium'], $trackingLog->metadata);
    }

    /**
     * Test that a TrackingLog belongs to a User.
     */
    public function test_tracking_log_belongs_to_user(): void
    {
        $user = User::factory()->create();
        $protocol = Protocol::factory()->create();

        $trackingLog = TrackingLog::create([
            'user_id' => $user->id,
            'protocol_id' => $protocol->id,
            'tracked_at' => '2023-10-27',
        ]);

        $this->assertInstanceOf(User::class, $trackingLog->user);
        $this->assertEquals($user->id, $trackingLog->user->id);
    }

    /**
     * Test that a TrackingLog belongs to a Protocol.
     */
    public function test_tracking_log_belongs_to_protocol(): void
    {
        $user = User::factory()->create();
        $protocol = Protocol::factory()->create();

        $trackingLog = TrackingLog::create([
            'user_id' => $user->id,
            'protocol_id' => $protocol->id,
            'tracked_at' => '2023-10-27',
        ]);

        $this->assertInstanceOf(Protocol::class, $trackingLog->protocol);
        $this->assertEquals($protocol->id, $trackingLog->protocol->id);
    }

    /**
     * Test the fillable attributes of the TrackingLog model.
     */
    public function test_tracking_log_fillable_attributes(): void
    {
        $trackingLog = new TrackingLog([
            'user_id' => 1,
            'protocol_id' => 1,
            'tracked_at' => '2023-10-27',
            'notes' => 'Some notes',
            'metadata' => ['key' => 'value'],
        ]);

        $this->assertEquals(1, $trackingLog->user_id);
        $this->assertEquals(1, $trackingLog->protocol_id);
        $this->assertEquals('2023-10-27', $trackingLog->tracked_at->format('Y-m-d'));
        $this->assertEquals('Some notes', $trackingLog->notes);
        $this->assertEquals(['key' => 'value'], $trackingLog->metadata);
    }

    /**
     * Test that the metadata attribute is cast to an array.
     */
    public function test_tracking_log_metadata_is_cast_to_array(): void
    {
        $user = User::factory()->create();
        $protocol = Protocol::factory()->create();

        $trackingLog = TrackingLog::create([
            'user_id' => $user->id,
            'protocol_id' => $protocol->id,
            'tracked_at' => '2023-10-27',
            'metadata' => json_encode(['test' => 'data']),
        ]);

        $this->assertIsArray($trackingLog->metadata);
        $this->assertEquals(['test' => 'data'], $trackingLog->metadata);
    }

    /**
     * Test that the unique constraint on user_id, protocol_id, and tracked_at is enforced.
     */
    public function test_tracking_log_unique_constraint(): void
    {
        $user = User::factory()->create();
        $protocol = Protocol::factory()->create();

        TrackingLog::create([
            'user_id' => $user->id,
            'protocol_id' => $protocol->id,
            'tracked_at' => '2023-10-27',
        ]);

        $this->expectException(\Illuminate\Database\QueryException::class);

        TrackingLog::create([
            'user_id' => $user->id,
            'protocol_id' => $protocol->id,
            'tracked_at' => '2023-10-27',
        ]);
    }
}
