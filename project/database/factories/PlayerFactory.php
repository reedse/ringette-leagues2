<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Player>
 */
class PlayerFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'user_id' => User::factory(), // Or null with some probability?
            'first_name' => fake()->firstName(),
            'last_name' => fake()->lastName(),
            'jersey_number' => fake()->optional()->numberBetween(1, 99), // Optional as per schema discussion?
            'position' => fake()->randomElement(['Forward', 'Defense', 'Center', 'Goalie', null]), // Allow null?
            'date_of_birth' => fake()->optional()->date(), // Adding date_of_birth which is in the schema
        ];
    }
}
