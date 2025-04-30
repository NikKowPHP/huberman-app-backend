<?php

namespace Tests\Unit\Console\Commands;

use App\Console\Commands\SendDueReminders;
use App\Modules\ProtocolEngine\Models\UserReminder;
use App\Modules\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SendDueRemindersTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_sends_reminders_for_daily_reminders_at_the_current_time(): void
    {
        $user = User::factory()->create();
        $reminder = UserReminder::factory()->forUser($user)->create([
            'reminder_time' => Carbon::now()->format('H:i:s'),
            'frequency' => 'daily',
            'is_active' => true,
        ]);

        Log::shouldReceive('info')
            ->once()
            ->with("Sending reminder {$reminder->id} to user {$reminder->user_id}");

        $this->artisan('reminders:send-due')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_sends_reminders_for_weekly_reminders_at_the_current_time(): void
    {
        $user = User::factory()->create();
        $reminder = UserReminder::factory()->forUser($user)->create([
            'reminder_time' => Carbon::now()->format('H:i:s'),
            'frequency' => 'weekly',
            'is_active' => true,
        ]);

        Log::shouldReceive('info')
            ->once()
            ->with("Sending reminder {$reminder->id} to user {$reminder->user_id}");

        $this->artisan('reminders:send-due')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_sends_reminders_for_specific_days_reminders_at_the_current_time_and_day(): void
    {
        $user = User::factory()->create();
        $currentDay = Carbon::now()->format('D');
        $reminder = UserReminder::factory()->forUser($user)->create([
            'reminder_time' => Carbon::now()->format('H:i:s'),
            'frequency' => 'specific_days',
            'specific_days' => [$currentDay],
            'is_active' => true,
        ]);

        Log::shouldReceive('info')
            ->once()
            ->with("Sending reminder {$reminder->id} to user {$reminder->user_id}");

        $this->artisan('reminders:send-due')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_does_not_send_reminders_if_not_the_current_time(): void
    {
        $user = User::factory()->create();
        $reminder = UserReminder::factory()->forUser($user)->create([
            'reminder_time' => Carbon::now()->addHour()->format('H:i:s'),
            'frequency' => 'daily',
            'is_active' => true,
        ]);

        Log::shouldReceive('info')
            ->never();

        $this->artisan('reminders:send-due')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_does_not_send_reminders_if_inactive(): void
    {
        $user = User::factory()->create();
        $reminder = UserReminder::factory()->forUser($user)->create([
            'reminder_time' => Carbon::now()->format('H:i:s'),
            'frequency' => 'daily',
            'is_active' => false,
        ]);

        Log::shouldReceive('info')
            ->never();

        $this->artisan('reminders:send-due')
            ->assertExitCode(0);
    }

    /** @test */
    public function it_does_not_send_reminders_for_specific_days_if_not_the_current_day(): void
    {
        $user = User::factory()->create();
        $currentDay = Carbon::now()->format('D');
        $reminder = UserReminder::factory()->forUser($user)->create([
            'reminder_time' => Carbon::now()->format('H:i:s'),
            'frequency' => 'specific_days',
            'specific_days' => [($currentDay === 'Mon' ? 'Tue' : 'Mon')], // Different day
            'is_active' => true,
        ]);

        Log::shouldReceive('info')
            ->never();

        $this->artisan('reminders:send-due')
            ->assertExitCode(0);
    }
}
