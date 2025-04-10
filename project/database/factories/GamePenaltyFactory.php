<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Game;
use App\Models\Player;
use App\Models\PenaltyCode;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\GamePenalty>
 */
class GamePenaltyFactory extends Factory
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
            'penalty_code_id' => PenaltyCode::factory(), // Or retrieve existing ones in seeder
            'period' => fake()->numberBetween(1, 2), // Assuming 2 periods
            'time_off_clock' => fake()->time('i:s'), // Format like '02:35'
        ];
    }
}
