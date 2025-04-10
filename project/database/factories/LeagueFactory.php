<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use App\Models\Association;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\League>
 */
class LeagueFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'association_id' => Association::factory(),
            'name' => fake()->randomElement(['U10', 'U12', 'U14', 'U16', 'U19', 'Open']) . ' ' . fake()->randomElement(['A', 'B', 'C']) . ' League',
            'website' => fake()->optional()->url(),
        ];
    }
}
