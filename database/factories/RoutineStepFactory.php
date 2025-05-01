<?php

namespace Database\Factories;

use App\Models\Routine;
use Illuminate\Database\Eloquent\Factories\Factory;

class RoutineStepFactory extends Factory
{
    public function definition()
    {
        return [
            'routine_id' => Routine::factory(),
            'name' => $this->faker->words(3, true),
            'description' => $this->faker->optional()->sentence(),
            'duration' => $this->faker->numberBetween(1, 30),
            'order' => $this->faker->unique()->numberBetween(1, 10),
            'is_optional' => $this->faker->boolean(20),
        ];
    }
}
