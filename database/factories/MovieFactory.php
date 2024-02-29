<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Movie>
 */
class MovieFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'title' => $this->faker->sentence,
            'description' => $this->faker->paragraph,
            'release_date' => $this->faker->date,
            'duration' => $this->faker->numberBetween(60, 180),
            'genre' => $this->faker->word,
            'director' => $this->faker->name,
            'cast' => $this->faker->name . ', ' . $this->faker->name . ', ' . $this->faker->name,
            'rating' => $this->faker->randomFloat(1, 0, 10),
            'poster' => $this->faker->imageUrl,
            'trailer' => $this->faker->url,
            'country' => $this->faker->country,
            'language' => $this->faker->languageCode,
        ];
    }
}
