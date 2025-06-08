<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Game;
use App\Models\User;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Clip>
 */
class ClipFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startTime = fake()->numberBetween(0, 1800); // Assuming max 30 min game segments for clips
        $endTime = $startTime + fake()->numberBetween(5, 60); // Clips are 5 to 60 seconds long

        return [
            'game_id' => Game::factory(),
            'coach_user_id' => User::factory(), // Clip creator
            'clip_title' => fake()->sentence(3),
            'video_url' => fake()->optional()->passthrough('https://www.youtube.com/watch?v=4sEF4e3_O7s'),
            'start_time_seconds' => $startTime,
            'end_time_seconds' => $endTime,
            'notes' => fake()->optional()->paragraph(),
        ];
    }
}
