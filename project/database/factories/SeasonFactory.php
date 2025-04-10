<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\League;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Season>
 */
class SeasonFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $startYear = fake()->numberBetween(2023, 2025);
        $endYear = $startYear + 1;
        return [
            'league_id' => League::factory(),
            'name' => $startYear . '-' . $endYear . ' Season',
            'start_date' => fake()->dateTimeBetween($startYear . '-09-01', $startYear . '-10-01'),
            'end_date' => fake()->dateTimeBetween($endYear . '-03-01', $endYear . '-04-01'),
        ];
    }
}
