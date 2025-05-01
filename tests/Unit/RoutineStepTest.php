<?php

namespace Tests\Unit;

use App\Models\Routine;
use App\Models\RoutineStep;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutineStepTest extends TestCase
{
    use RefreshDatabase;

    public function test_step_belongs_to_routine()
    {
        $routine = Routine::factory()->create();
        $step = RoutineStep::factory()->create(['routine_id' => $routine->id]);

        $this->assertInstanceOf(Routine::class, $step->routine);
        $this->assertEquals($routine->id, $step->routine->id);
    }

    public function test_fillable_fields()
    {
        $step = new RoutineStep([
            'routine_id' => 1,
            'name' => 'Morning Routine',
            'description' => 'Start your day right',
            'duration' => 30,
            'order' => 1,
            'is_optional' => false
        ]);

        $this->assertEquals(1, $step->routine_id);
        $this->assertEquals('Morning Routine', $step->name);
        $this->assertEquals('Start your day right', $step->description);
        $this->assertEquals(30, $step->duration);
        $this->assertEquals(1, $step->order);
        $this->assertFalse($step->is_optional);
    }

    public function test_casts()
    {
        $step = RoutineStep::factory()->create([
            'duration' => '30',
            'order' => '1',
            'is_optional' => '1'
        ]);

        $this->assertIsInt($step->duration);
        $this->assertIsInt($step->order);
        $this->assertIsBool($step->is_optional);
    }
}
