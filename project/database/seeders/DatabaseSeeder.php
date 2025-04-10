<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Role;
use App\Models\Player;
use App\Models\Association;
use App\Models\League;
use App\Models\Season;
use App\Models\Team;
use App\Models\Game;
use App\Models\PenaltyCode;
use App\Models\Clip;
use App\Models\GamePenalty;
use App\Models\PlayerGameStat;
// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // --- Clear existing data (optional, be careful with foreign keys) ---
        // DB::statement('SET FOREIGN_KEY_CHECKS=0;'); // Disable FK checks for truncation
        // Clip::truncate(); // Truncate tables in reverse order of dependencies
        // GamePenalty::truncate();
        // PlayerGameStat::truncate();
        // DB::table('roster_entries')->truncate();
        // DB::table('clip_player')->truncate();
        // Game::truncate();
        // Team::truncate();
        // Season::truncate();
        // League::truncate();
        // Association::truncate();
        // Player::truncate();
        // DB::table('role_user')->truncate();
        // Role::truncate();
        // User::truncate();
        // PenaltyCode::truncate();
        // DB::statement('SET FOREIGN_KEY_CHECKS=1;'); // Re-enable FK checks

        // --- Create Roles (ensure they are not duplicated) ---
        $adminRole = Role::firstOrCreate(['name' => 'Admin']);
        $coachRole = Role::firstOrCreate(['name' => 'Coach']);
        $playerRole = Role::firstOrCreate(['name' => 'Player']);

        // --- Create Penalty Codes ---
        $penaltyCodes = PenaltyCode::factory()->count(10)->create();

        // --- Create Users ---
        $adminUser = User::factory()->create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);
        $adminUser->roles()->attach($adminRole);

        $testPlayerUser = User::factory()->create([
            'name' => 'Test Player',
            'email' => 'player@example.com',
            'password' => Hash::make('password'),
        ]);
        $testPlayerUser->roles()->attach($playerRole);

        // --- Create Associations, Leagues, Seasons, Teams, Players, Games ---
        Association::factory()->count(2)->create()->each(function ($association) use ($coachRole, $playerRole, $testPlayerUser, $penaltyCodes) {
            League::factory()->count(3)->for($association)->create()->each(function ($league) use ($coachRole, $playerRole, $testPlayerUser, $penaltyCodes) {
                Season::factory()->count(2)->for($league)->create()->each(function ($season) use ($coachRole, $playerRole, $testPlayerUser, $penaltyCodes, $league) {
                    $teams = Team::factory()->count(4)->for($season)->create();

                    $allPlayersInSeason = collect(); // Collect all players created in this season

                    $teams->each(function ($team) use ($coachRole, $playerRole, $testPlayerUser, &$allPlayersInSeason, $season) {
                        // Create a Coach User for the team
                        $coachUser = User::factory()->create([
                            'name' => 'Coach ' . $team->name,
                            'email' => 'coach.' . strtolower(str_replace(' ', '', $team->name)) . '@example.com',
                            'password' => Hash::make('password'),
                            'managed_team_id' => $team->id,
                        ]);
                        $coachUser->roles()->attach($coachRole);

                        // Create Players for the team
                        $players = Player::factory()->count(fake()->numberBetween(12, 15))->create();

                        // Attach players to the team roster with season_id
                        $players->each(function ($player) use ($team, $season) {
                            $team->players()->attach($player->id, ['season_id' => $season->id]);
                        });
                        $allPlayersInSeason = $allPlayersInSeason->merge($players);

                        // Assign the Test Player to the first team in the first season of the first league of the first association
                        if ($team->id === Team::oldest()->first()->id) {
                             // Create a player record for the test user if it doesn't exist
                            $playerProfile = Player::firstOrCreate(
                                ['user_id' => $testPlayerUser->id],
                                Player::factory()->make(['user_id' => $testPlayerUser->id])->toArray()
                            );
                            if (!$team->players->contains($playerProfile->id)) {
                                $team->players()->attach($playerProfile->id, ['season_id' => $season->id]);
                                $allPlayersInSeason->push($playerProfile);
                            }
                        }
                    });

                    // Create Games for the Season
                    $teamIds = $teams->pluck('id');
                    for ($i = 0; $i < 10; $i++) {
                        $homeTeamId = $teamIds->random();
                        $awayTeamId = $teamIds->filter(fn ($id) => $id !== $homeTeamId)->random();

                        $game = Game::factory()->create([
                            'season_id' => $season->id,
                            'league_id' => $league->id,
                            'home_team_id' => $homeTeamId,
                            'away_team_id' => $awayTeamId,
                             // Override status sometimes
                             'status' => fake()->randomElement(['Scheduled', 'Completed', 'Completed', 'Completed']), // Skew towards completed
                             'game_date_time' => fake()->dateTimeBetween($season->start_date, $season->end_date),
                        ]);

                        // If game is completed, add stats and penalties
                        if ($game->status === 'Completed') {
                            $homeTeamPlayers = Team::find($homeTeamId)->players;
                            $awayTeamPlayers = Team::find($awayTeamId)->players;
                            $gamePlayers = $homeTeamPlayers->merge($awayTeamPlayers);

                            // Add Player Game Stats
                            $gamePlayers->each(function ($player) use ($game, $homeTeamId, $awayTeamId) {
                                // Determine which team the player belongs to
                                $playerTeams = $player->teams()->pluck('teams.id')->toArray();
                                $teamId = in_array($homeTeamId, $playerTeams) ? $homeTeamId : 
                                         (in_array($awayTeamId, $playerTeams) ? $awayTeamId : null);
                                
                                PlayerGameStat::factory()->create([
                                    'game_id' => $game->id,
                                    'player_id' => $player->id,
                                    'team_id' => $teamId,
                                ]);
                            });

                            // Add Game Penalties (randomly)
                            if ($gamePlayers->isNotEmpty() && fake()->boolean(75)) { // 75% chance of penalties
                                // Get a random player for penalties
                                $randomPlayer = $gamePlayers->random();
                                
                                // Determine player's team for this game
                                $playerTeams = $randomPlayer->teams()->pluck('teams.id')->toArray();
                                $playerTeamId = in_array($homeTeamId, $playerTeams) ? $homeTeamId : 
                                             (in_array($awayTeamId, $playerTeams) ? $awayTeamId : null);
                                
                                if ($playerTeamId) {
                                    GamePenalty::factory()->count(fake()->numberBetween(1, 5))->create([
                                        'game_id' => $game->id,
                                        'player_id' => $randomPlayer->id,
                                        'team_id' => $playerTeamId,
                                        'penalty_code_id' => $penaltyCodes->random()->id,
                                    ]);
                                }
                            }

                            // Create Clips for the game (randomly)
                            if (fake()->boolean(50)) { // 50% chance of having clips
                                Clip::factory()->count(fake()->numberBetween(1, 4))->create([
                                    'game_id' => $game->id,
                                    'coach_user_id' => User::whereHas('roles', fn($q) => $q->where('name', 'Coach'))->inRandomOrder()->first()?->id ?? User::factory()->create()->id, // Assign to a random coach or new user
                                ])->each(function ($clip) use ($gamePlayers) {
                                    // Link players to the clip (random subset of players in the game)
                                    if ($gamePlayers->isNotEmpty()) {
                                        $clipPlayers = $gamePlayers->random(fake()->numberBetween(1, min(3, $gamePlayers->count())));
                                        $clip->sharedWithPlayers()->attach($clipPlayers->pluck('id'), [
                                            'coach_note' => fake()->optional()->sentence(),
                                            'sent_at' => now()->subHours(fake()->numberBetween(1, 48)),
                                            'created_at' => now(),
                                            'updated_at' => now()
                                        ]);
                                    }
                                });
                            }
                        }
                    }
                });
            });
        });
    }
}
