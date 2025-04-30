<?php

namespace Database\Factories;

use App\Modules\ProtocolEngine\Models\UserReminder;
use App\Modules\UserManagement\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Modules\ProtocolEngine\Models\UserReminder>
 */
class UserReminderFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var class-string<\App\Modules\ProtocolEngine\Models\UserReminder>
     */
    protected $model = UserReminder::class;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(),
            'protocol_id' => null, // Or Protocol::factory() if required, adjust as needed
            'reminder_time' => $this->faker->time('H:i:s'),
            'frequency' => $this->faker->randomElement(['daily', 'weekly', 'specific_days']),
            'specific_days' => function (array $attributes) {
                return $attributes['frequency'] === 'specific_days'
                    ? $this->faker->randomElements(['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun'], $this->faker->numberBetween(1, 7))
                    : null;
            },
            'message' => $this->faker->sentence,
            'is_active' => $this->faker->boolean(80), // Mostly active
        ];
    }

    /**
     * Indicate that the reminder belongs to a specific user.
     */
    public function forUser(User $user): static
    {
        return $this->state(fn (array $attributes) => [
            'user_id' => $user->id,
        ]);
    }
}
