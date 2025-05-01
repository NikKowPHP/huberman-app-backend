<?php

namespace Tests\Unit;

use App\Models\Routine;
use App\Models\User;
use App\Policies\RoutinePolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoutinePolicyTest extends TestCase
{
    use RefreshDatabase;

    private RoutinePolicy $policy;

    protected function setUp(): void
    {
        parent::setUp();
        $this->policy = new RoutinePolicy();
    }

    public function test_view_any()
    {
        $user = User::factory()->create();
        $this->assertTrue($this->policy->viewAny($user));
    }

    public function test_view_own_routine()
    {
        $owner = User::factory()->create();
        $routine = Routine::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($this->policy->view($owner, $routine));
    }

    public function test_view_other_user_routine()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $routine = Routine::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($this->policy->view($otherUser, $routine));
    }

    public function test_create()
    {
        $user = User::factory()->create();
        $this->assertTrue($this->policy->create($user));
    }

    public function test_update_own_routine()
    {
        $owner = User::factory()->create();
        $routine = Routine::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($this->policy->update($owner, $routine));
    }

    public function test_update_other_user_routine()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $routine = Routine::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($this->policy->update($otherUser, $routine));
    }

    public function test_delete_own_routine()
    {
        $owner = User::factory()->create();
        $routine = Routine::factory()->create(['user_id' => $owner->id]);

        $this->assertTrue($this->policy->delete($owner, $routine));
    }

    public function test_delete_other_user_routine()
    {
        $owner = User::factory()->create();
        $otherUser = User::factory()->create();
        $routine = Routine::factory()->create(['user_id' => $owner->id]);

        $this->assertFalse($this->policy->delete($otherUser, $routine));
    }
}
