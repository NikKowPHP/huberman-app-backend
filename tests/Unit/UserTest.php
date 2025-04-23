<?php

namespace Tests\Unit;

use App\Modules\UserManagement\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_user()
    {
        $user = User::factory()->create();

        $this->assertInstanceOf(User::class, $user);
        $this->assertNotNull($user->id);
    }

    /** @test */
    public function it_has_relationships()
    {
        $user = User::factory()->create();

        $this->assertTrue(method_exists($user, 'subscriptions'));
        $this->assertTrue(method_exists($user, 'notes'));
        $this->assertTrue(method_exists($user, 'reminders'));
        $this->assertTrue(method_exists($user, 'tracking'));
    }

    /** @test */
    public function it_has_correct_attributes()
    {
        $user = User::factory()->create([
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => bcrypt('password'),
        ]);

        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertTrue(\Hash::check('password', $user->password));
    }

    /** @test */
    public function it_has_correct_fillable_properties()
    {
        $user = new User();

        $fillable = $user->getFillable();

        $this->assertContains('name', $fillable);
        $this->assertContains('email', $fillable);
        $this->assertContains('password', $fillable);
    }

    /** @test */
    public function it_has_correct_hidden_properties()
    {
        $user = new User();

        $hidden = $user->getHidden();

        $this->assertContains('password', $hidden);
        $this->assertContains('remember_token', $hidden);
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $user = new User();

        $casts = $user->getCasts();

        $this->assertArrayHasKey('email_verified_at', $casts);
        $this->assertEquals('datetime', $casts['email_verified_at']);
    }

    /** @test */
    public function it_has_correct_with_relations()
    {
        $user = new User();

        $with = $user->getWith();

        $this->assertContains('subscriptions', $with);
    }
}
