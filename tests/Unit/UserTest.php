<?php

namespace Tests\Unit;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function a_user_can_be_created()
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->name);
        $this->assertNotNull($user->email);
        $this->assertNotNull($user->password);
    }

    /** @test */
    public function a_user_has_relationships()
    {
        $user = User::factory()->create();

        // Initially Subscription, Notes, Reminders, Tracking
        $this->assertTrue(method_exists($user, 'subscriptions'));
        // $this->assertTrue(method_exists($user, 'notes')); //TODO: Implement Notes
        // $this->assertTrue(method_exists($user, 'reminders')); //TODO: Implement Reminders
        // $this->assertTrue(method_exists($user, 'tracking')); //TODO: Implement Tracking
    }

    /** @test */
    public function a_user_has_attributes()
    {
        $user = User::factory()->create();

        $this->assertIsString($user->name);
        $this->assertIsString($user->email);
        $this->assertIsString($user->password);
        $this->assertNull($user->email_verified_at);
    }

    /** @test */
    public function a_user_has_fillable_properties()
    {
        $user = new User();

        $this->assertEquals([
            'name',
            'email',
            'password',
        ], $user->getFillable());
    }

    /** @test */
    public function a_user_has_hidden_properties()
    {
        $user = new User();

        $this->assertEquals([
            'password',
            'remember_token',
        ], $user->getHidden());
    }

    /** @test */
    public function a_user_has_casts()
    {
        $user = new User();

        $this->assertEquals([
            'email_verified_at' => 'datetime',
        ], $user->getCasts());
    }

    /** @test */
    public function a_user_has_with_relations()
    {
        $user = new User();

        $this->assertEquals([], $user->getWith());
    }
}
