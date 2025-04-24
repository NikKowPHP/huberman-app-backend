<?php

namespace Tests\Unit\Modules\UserManagement; // Updated namespace

use App\Modules\UserManagement\Models\User; // Updated namespace
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;
use Illuminate\Database\Eloquent\Relations\HasMany; // Added for relationship type hinting

class UserTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_can_create_a_user_using_factory()
    {
        $user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'password', // Factory handles hashing
        ]);

        $this->assertInstanceOf(User::class, $user);
        $this->assertDatabaseHas('users', [
            'email' => 'test@example.com',
            'name' => 'Test User',
        ]);
        $this->assertTrue(Hash::check('password', $user->password)); // Verify password hashing
    }

    /** @test */
    public function it_defines_expected_relationships()
    {
        $user = User::factory()->make(); // Use make() as we only need the instance, not DB record

        // Check if relationship methods exist and return the correct type
        $this->assertTrue(method_exists($user, 'subscriptions'));
        $this->assertInstanceOf(HasMany::class, $user->subscriptions());

        $this->assertTrue(method_exists($user, 'notes'));
        $this->assertInstanceOf(HasMany::class, $user->notes());

        $this->assertTrue(method_exists($user, 'reminders'));
        $this->assertInstanceOf(HasMany::class, $user->reminders());

        $this->assertTrue(method_exists($user, 'trackingLogs')); // Updated method name
        $this->assertInstanceOf(HasMany::class, $user->trackingLogs());
    }

    /** @test */
    public function it_has_correct_attributes_and_types()
    {
        $user = User::factory()->create([
            'profile_picture_url' => 'http://example.com/pic.jpg',
            'email_verified_at' => now(), // Use verified state for this test
        ]);

        $this->assertIsString($user->name);
        $this->assertIsString($user->email);
        $this->assertIsString($user->password); // Hashed password is a string
        $this->assertIsString($user->profile_picture_url);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->email_verified_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->created_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->updated_at);
        $this->assertNull($user->deleted_at); // Check soft delete default

        // Test soft delete
        $user->delete();
        $this->assertNotNull($user->deleted_at);
        $this->assertInstanceOf(\Illuminate\Support\Carbon::class, $user->deleted_at);
    }

    /** @test */
    public function it_has_correct_fillable_properties()
    {
        $user = new User(); // Instantiating directly is fine for checking fillable

        $this->assertEquals([
            'name',
            'email',
            'password',
            'profile_picture_url', // Added
        ], $user->getFillable());
    }

    /** @test */
    public function it_has_correct_hidden_properties()
    {
        $user = new User();

        $this->assertEquals([
            'password',
            'remember_token',
        ], $user->getHidden());
    }

    /** @test */
    public function it_has_correct_casts()
    {
        $user = new User();
        $casts = $user->getCasts(); // Use getCasts() for protected method access

        $this->assertEquals('datetime', $casts['email_verified_at']);
        $this->assertEquals('hashed', $casts['password']); // Check password cast added by Authenticatable
        $this->assertArrayHasKey('deleted_at', $casts); // SoftDeletes adds this cast
        $this->assertEquals('datetime', $casts['deleted_at']);
    }

    /** @test */
    public function it_has_correct_with_relations_property()
    {
        $user = new User();

        // Access protected property via reflection or getter if available
        // In this case, $with is public/protected, so direct access or getter might work
        // If strictly checking the default, reflection is safer. Let's assume direct access/getter for simplicity.
        // $reflector = new \ReflectionClass($user);
        // $withProperty = $reflector->getProperty('with');
        // $withProperty->setAccessible(true);
        // $this->assertEquals([], $withProperty->getValue($user));

        // Or if using a getter (not default in Laravel)
        // $this->assertEquals([], $user->getWithRelations());

        // Check default value directly if accessible (often protected)
        // For this test, let's assert the expected default value directly based on the model code
        $this->assertEquals([], $user->getRelations()); // Check initially loaded relations
        // To check the $with property itself:
        $reflector = new \ReflectionClass(User::class);
        $withProperty = $reflector->getProperty('with');
        $withProperty->setAccessible(true); // Allow access to protected property
        $this->assertEquals([], $withProperty->getValue(new User()));
    }

     /** @test */
    public function it_uses_soft_deletes_trait()
    {
        $this->assertTrue(in_array(
            \Illuminate\Database\Eloquent\SoftDeletes::class,
            class_uses_recursive(User::class)
        ));
    }

     /** @test */
    public function it_uses_has_api_tokens_trait()
    {
        $this->assertTrue(in_array(
            \Laravel\Sanctum\HasApiTokens::class,
            class_uses_recursive(User::class)
        ));
    }
}