<?php

namespace Database\Factories;

use App\Modules\ContentManagement\Models\Protocol;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProtocolFactory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected $model = Protocol::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            'name' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'implementation_guide' => $this->faker->paragraph,
            'is_free' => $this->faker->boolean,
        ];
    }
}
