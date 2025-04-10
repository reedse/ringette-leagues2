<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Season;
use App\Models\Team;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Game>
 */
class GameFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // If a season is provided via state(), we'll use that; otherwise create one
        $season = $this->season ?? Season::factory()->create();
        // Get the league from the season
        $league = $season->league;
        $homeTeam = Team::factory()->state(['season_id' => $season->id])->create();
        $awayTeam = Team::factory()->state(['season_id' => $season->id])->create(); // Ensure this is different if possible, harder with simple factory call

        $status = fake()->randomElement(['Scheduled', 'Completed']);
        $gameDate = fake()->dateTimeBetween($season->start_date, $season->end_date);

        return [
            'season_id' => $season->id,
            'league_id' => $league->id,
            'home_team_id' => $homeTeam->id,
            'away_team_id' => $awayTeam->id,
            'game_date_time' => $gameDate,
            'status' => $status,
            'home_score' => $status === 'Completed' ? fake()->numberBetween(0, 15) : null,
            'away_score' => $status === 'Completed' ? fake()->numberBetween(0, 15) : null,
            'location' => fake()->optional()->city() . ' Arena',
            'video_url' => fake()->optional()->url(),
        ];
    }
}
