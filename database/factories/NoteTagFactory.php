<?php

namespace Database\Factories;

use App\Models\NoteTag;
use Illuminate\Database\Eloquent\Factories\Factory;

class NoteTagFactory extends Factory
{
    protected $model = NoteTag::class;

    public function definition()
    {
        return [
            'name' => $this->faker->word,
            'color' => $this->faker->hexColor,
        ];
    }
}
