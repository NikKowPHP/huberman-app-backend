<?php

namespace Tests\Unit;

use App\Models\User;
use App\Models\NoteTag;
use App\Policies\NoteTagPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteTagPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_view_any_returns_true()
    {
        $user = User::factory()->create();
        $policy = new NoteTagPolicy();

        $this->assertTrue($policy->viewAny($user));
    }

    public function test_view_own_note_tag()
    {
        $user = User::factory()->create();
        $noteTag = NoteTag::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->can('view', $noteTag));
    }

    public function test_view_other_user_note_tag()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $noteTag = NoteTag::factory()->create(['user_id' => $otherUser->id]);

        $this->assertFalse($user->can('view', $noteTag));
    }

    public function test_create_returns_true()
    {
        $user = User::factory()->create();
        $policy = new NoteTagPolicy();

        $this->assertTrue($policy->create($user));
    }

    public function test_update_own_note_tag()
    {
        $user = User::factory()->create();
        $noteTag = NoteTag::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->can('update', $noteTag));
    }

    public function test_update_other_user_note_tag()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $noteTag = NoteTag::factory()->create(['user_id' => $otherUser->id]);

        $this->assertFalse($user->can('update', $noteTag));
    }

    public function test_delete_own_note_tag()
    {
        $user = User::factory()->create();
        $noteTag = NoteTag::factory()->create(['user_id' => $user->id]);

        $this->assertTrue($user->can('delete', $noteTag));
    }

    public function test_delete_other_user_note_tag()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $noteTag = NoteTag::factory()->create(['user_id' => $otherUser->id]);

        $this->assertFalse($user->can('delete', $noteTag));
    }
}
