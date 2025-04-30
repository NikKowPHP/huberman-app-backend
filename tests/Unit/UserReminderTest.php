<?php

namespace Tests\Unit;

use PHPUnit\Framework\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use App\Modules\ProtocolEngine\Models\UserReminder;
use App\Modules\UserManagement\Models\User;
use App\Modules\ContentManagement\Models\Protocol;

class UserReminderTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test UserReminder model attributes.
     *
     * @return void
     */
    public function test_user_reminder_attributes(): void
    {
        $reminder = UserReminder::factory()->create([
            'scheduled_at' => now()->addDay(),
            'sent_at' => null,
            'message' => 'Test reminder message',
        ]);

        $this->assertNotNull($reminder->id);
        $this->assertNotNull($reminder->user_id);
        $this->assertNotNull($reminder->protocol_id);
        $this->assertNotNull($reminder->scheduled_at);
        $this->assertNull($reminder->sent_at);
        $this->assertEquals('Test reminder message', $reminder->message);
        $this->assertNotNull($reminder->created_at);
        $this->assertNotNull($reminder->updated_at);
    }

    /**
     * Test UserReminder model relationships.
     *
     * @return void
     */
    public function test_user_reminder_relationships(): void
    {
        $user = User::factory()->create();
        $protocol = Protocol::factory()->create();

        $reminder = UserReminder::factory()->create([
            'user_id' => $user->id,
            'protocol_id' => $protocol->id,
        ]);

        $this->assertInstanceOf(User::class, $reminder->user);
        $this->assertEquals($user->id, $reminder->user->id);

        $this->assertInstanceOf(Protocol::class, $reminder->protocol);
        $this->assertEquals($protocol->id, $reminder->protocol->id);
    }
}
