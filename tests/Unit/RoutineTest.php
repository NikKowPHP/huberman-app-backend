<?php

namespace Tests\Unit;

use App\Models\Routine;
use App\Models\RoutineStep;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutineTest extends TestCase
{
    use RefreshDatabase;

    public function test_routine_has_user()
    {
        $user = User::factory()->create();
        $routine = Routine::factory()->create(['user_id' => $user->id]);

        $this->assertInstanceOf(User::class, $routine->user);
        $this->assertEquals($user->id, $routine->user->id);
    }

    public function test_routine_has_steps()
    {
        $routine = Routine::factory()
            ->has(RoutineStep::factory()->count(3))
            ->create();

        $this->assertCount(3, $routine->steps);
        $this->assertInstanceOf(RoutineStep::class, $routine->steps->first());
    }

    public function test_fillable_fields()
    {
        $routine = new Routine([
            'user_id' => 1,
            'name' => 'Morning Routine',
            'description' => 'Start your day right',
            'frequency' => 'daily',
            'start_time' => '07:00:00',
            'end_time' => '08:00:00',
            'is_active' => true
        ]);

        $this->assertEquals('Morning Routine', $routine->name);
        $this->assertEquals('Start your day right', $routine->description);
        $this->assertEquals('daily', $routine->frequency);
        $this->assertEquals('07:00:00', $routine->start_time->format('H:i:s'));
        $this->assertEquals('08:00:00', $routine->end_time->format('H:i:s'));
        $this->assertTrue($routine->is_active);
    }

    public function test_casts()
    {
        $routine = Routine::factory()->create([
            'is_active' => 1,
            'start_time' => '07:00:00',
            'end_time' => '08:00:00'
        ]);

        $this->assertTrue($routine->is_active);
        $this->assertEquals('07:00', $routine->start_time->format('H:i'));
        $this->assertEquals('08:00', $routine->end_time->format('H:i'));
    }
}
