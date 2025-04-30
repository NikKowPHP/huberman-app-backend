<?php

namespace Tests\Unit;

use App\Modules\NotesService\Models\Note;
use App\Policies\NotePolicy;
use App\Modules\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NotePolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_policy_allows_owner()
    {
        $user = User::factory()->create();
        $note = Note::factory()->create(['user_id' => $user->id]);
        $policy = new NotePolicy();
        $this->assertTrue($policy->view($user, $note));
    }

    public function test_view_policy_denies_non_owner()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $note = Note::factory()->create(['user_id' => $otherUser->id]);
        $policy = new NotePolicy();
        $this->assertFalse($policy->view($user, $note));
    }

    public function test_update_policy_allows_owner()
    {
        $user = User::factory()->create();
        $note = Note::factory()->create(['user_id' => $user->id]);
        $policy = new NotePolicy();
        $this->assertTrue($policy->update($user, $note));
    }

    public function test_update_policy_denies_non_owner()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $note = Note::factory()->create(['user_id' => $otherUser->id]);
        $policy = new NotePolicy();
        $this->assertFalse($policy->update($user, $note));
    }

    public function test_delete_policy_allows_owner()
    {
        $user = User::factory()->create();
        $note = Note::factory()->create(['user_id' => $user->id]);
        $policy = new NotePolicy();
        $this->assertTrue($policy->delete($user, $note));
    }

    public function test_delete_policy_denies_non_owner()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $note = Note::factory()->create(['user_id' => $otherUser->id]);
        $policy = new NotePolicy();
        $this->assertFalse($policy->delete($user, $note));
    }

    public function test_create_policy_allows_all()
    {
        $user = User::factory()->create();
        $policy = new NotePolicy();
        $this->assertTrue($policy->create($user));
    }
}
