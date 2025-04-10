<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Season;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Team>
 */
class TeamFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'association_id' => \App\Models\Association::factory(),
            'league_id' => \App\Models\League::factory(),
            'season_id' => Season::factory(),
            'name' => fake()->city() . ' ' . fake()->randomElement(['Flames', 'Jets', 'Storm', 'Ice', 'Wild']),
        ];
    }
}
