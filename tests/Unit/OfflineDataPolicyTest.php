<?php

namespace Tests\Unit;

use App\Models\User;
use App\OfflineData;
use App\Policies\OfflineDataPolicy;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OfflineDataPolicyTest extends TestCase
{
    use RefreshDatabase;

    public function test_viewAny_returns_true()
    {
        $user = User::factory()->create();
        $policy = new OfflineDataPolicy();

        $this->assertTrue($policy->viewAny($user));
    }

    public function test_view_allows_access_to_own_offline_data()
    {
        $user = User::factory()->create();
        $offlineData = OfflineData::factory()->create(['user_id' => $user->id]);
        $policy = new OfflineDataPolicy();

        $this->assertTrue($policy->view($user, $offlineData));
    }

    public function test_view_denies_access_to_others_offline_data()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $offlineData = OfflineData::factory()->create(['user_id' => $otherUser->id]);
        $policy = new OfflineDataPolicy();

        $this->assertFalse($policy->view($user, $offlineData));
    }

    public function test_create_returns_true()
    {
        $user = User::factory()->create();
        $policy = new OfflineDataPolicy();

        $this->assertTrue($policy->create($user));
    }

    public function test_update_allows_update_to_own_offline_data()
    {
        $user = User::factory()->create();
        $offlineData = OfflineData::factory()->create(['user_id' => $user->id]);
        $policy = new OfflineDataPolicy();

        $this->assertTrue($policy->update($user, $offlineData));
    }

    public function test_update_denies_update_to_others_offline_data()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $offlineData = OfflineData::factory()->create(['user_id' => $otherUser->id]);
        $policy = new OfflineDataPolicy();

        $this->assertFalse($policy->update($user, $offlineData));
    }

    public function test_delete_allows_delete_to_own_offline_data()
    {
        $user = User::factory()->create();
        $offlineData = OfflineData::factory()->create(['user_id' => $user->id]);
        $policy = new OfflineDataPolicy();

        $this->assertTrue($policy->delete($user, $offlineData));
    }

    public function test_delete_denies_delete_to_others_offline_data()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $offlineData = OfflineData::factory()->create(['user_id' => $otherUser->id]);
        $policy = new OfflineDataPolicy();

        $this->assertFalse($policy->delete($user, $offlineData));
    }

    public function test_restore_allows_restore_to_own_offline_data()
    {
        $user = User::factory()->create();
        $offlineData = OfflineData::factory()->create(['user_id' => $user->id]);
        $policy = new OfflineDataPolicy();

        $this->assertTrue($policy->restore($user, $offlineData));
    }

    public function test_restore_denies_restore_to_others_offline_data()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $offlineData = OfflineData::factory()->create(['user_id' => $otherUser->id]);
        $policy = new OfflineDataPolicy();

        $this->assertFalse($policy->restore($user, $offlineData));
    }

    public function test_forceDelete_allows_forceDelete_to_own_offline_data()
    {
        $user = User::factory()->create();
        $offlineData = OfflineData::factory()->create(['user_id' => $user->id]);
        $policy = new OfflineDataPolicy();

        $this->assertTrue($policy->forceDelete($user, $offlineData));
    }

    public function test_forceDelete_denies_forceDelete_to_others_offline_data()
    {
        $user = User::factory()->create();
        $otherUser = User::factory()->create();
        $offlineData = OfflineData::factory()->create(['user_id' => $otherUser->id]);
        $policy = new OfflineDataPolicy();

        $this->assertFalse($policy->forceDelete($user, $offlineData));
    }
}
