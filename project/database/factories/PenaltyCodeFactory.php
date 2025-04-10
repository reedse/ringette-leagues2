<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PenaltyCode>
 */
class PenaltyCodeFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('??##'), // Example: AB12
            'name' => fake()->sentence(4), // Use 'name' instead of 'description'
            'default_minutes' => fake()->randomElement([2, 4, 5]), // Add default_minutes
        ];
    }
}
