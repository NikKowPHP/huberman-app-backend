<?php

namespace Tests\Unit;

use App\Models\User;
use App\Modules\NotesService\Models\NoteCategory;
use App\Policies\NoteCategoryPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class NoteCategoryPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewAny_returns_true()
    {
        $user = User::factory()->create();
        $policy = new NoteCategoryPolicy();

        $this->assertTrue($policy->viewAny($user));
    }

    public function test_view_allows_access_to_own_noteCategory()
    {
        $user = User::factory()->create();
        $noteCategory = NoteCategory::factory()->create(['user_id' => $user->id]);
        $policy = new NoteCategoryPolicy();

        $this->assertTrue($policy->view($user, $noteCategory));
    }

    public function test_view_denies_access_to_others_noteCategory()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $noteCategory = NoteCategory::factory()->create(['user_id' => $otherUser->id]);
        $policy = new NoteCategoryPolicy();

        $this->assertFalse($policy->view($user, $noteCategory));
    }

    public function test_create_returns_true()
    {
        $user = User::factory()->create();
        $policy = new NoteCategoryPolicy();

        $this->assertTrue($policy->create($user));
    }

    public function test_update_allows_update_to_own_noteCategory()
    {
        $user = User::factory()->create();
        $noteCategory = NoteCategory::factory()->create(['user_id' => $user->id]);
        $policy = new NoteCategoryPolicy();

        $this->assertTrue($policy->update($user, $noteCategory));
    }

    public function test_update_denies_update_to_others_noteCategory()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $noteCategory = NoteCategory::factory()->create(['user_id' => $otherUser->id]);
        $policy = new NoteCategoryPolicy();

        $this->assertFalse($policy->update($user, $noteCategory));
    }

    public function test_delete_allows_delete_to_own_noteCategory()
    {
        $user = User::factory()->create();
        $noteCategory = NoteCategory::factory()->create(['user_id' => $user->id]);
        $policy = new NoteCategoryPolicy();

        $this->assertTrue($policy->delete($user, $noteCategory));
    }

    public function test_delete_denies_delete_to_others_noteCategory()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $noteCategory = NoteCategory::factory()->create(['user_id' => $otherUser->id]);
        $policy = new NoteCategoryPolicy();

        $this->assertFalse($policy->delete($user, $noteCategory));
    }

    public function test_restore_allows_restore_to_own_noteCategory()
    {
        $user = User::factory()->create();
        $noteCategory = NoteCategory::factory()->create(['user_id' => $user->id]);
        $policy = new NoteCategoryPolicy();

        $this->assertTrue($policy->restore($user, $noteCategory));
    }

    public function test_restore_denies_restore_to_others_noteCategory()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $noteCategory = NoteCategory::factory()->create(['user_id' => $otherUser->id]);
        $policy = new NoteCategoryPolicy();

        $this->assertFalse($policy->restore($user, $noteCategory));
    }

    public function test_forceDelete_allows_forceDelete_to_own_noteCategory()
    {
        $user = User::factory()->create();
        $noteCategory = NoteCategory::factory()->create(['user_id' => $user->id]);
        $policy = new NoteCategoryPolicy();

        $this->assertTrue($policy->forceDelete($user, $noteCategory));
    }

    public function test_forceDelete_denies_forceDelete_to_others_noteCategory()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $noteCategory = NoteCategory::factory()->create(['user_id' => $otherUser->id]);
        $policy = new NoteCategoryPolicy();

        $this->assertFalse($policy->forceDelete($user, $noteCategory));
    }
}
