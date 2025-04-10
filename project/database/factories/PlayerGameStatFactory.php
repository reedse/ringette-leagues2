<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Game;
use App\Models\Player;
use App\Models\Team;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\PlayerGameStat>
 */
class PlayerGameStatFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Note: game_id and player_id will typically be set when calling the factory
        return [
            'goals' => fake()->numberBetween(0, 5),
            'assists' => fake()->numberBetween(0, 5),
            'shots_on_goal' => fake()->numberBetween(0, 10),
            'saves' => fake()->numberBetween(0, 15),
            'goals_against' => fake()->numberBetween(0, 5),
        ];
    }
} 