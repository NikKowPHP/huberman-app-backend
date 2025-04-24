<?php

namespace Database\Factories;

use App\Modules\UserManagement\Models\User; // Updated namespace
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\UserManagement\Models\User> // Updated namespace
 */
class UserFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\Illuminate\Database\Eloquent\Model>
     */
    protected $model = User::class; // Explicitly set model

    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => null, // Default to null as per test expectation
            'password' => static::$password ??= Hash::make('password'),
            'profile_picture_url' => null, // Added field
            'remember_token' => Str::random(10),
        ];
    }

    /**
     * Indicate that the model's email address should be verified.
     */
    public function verified(): static // Changed from unverified to verified for clarity
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => now(),
        ]);
    }
}