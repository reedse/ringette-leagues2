<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use App\Models\PlayerGameStat;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PlayerStatsController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $player = Player::where('user_id', $user->id)->first();
        
        if (!$player) {
            return Inertia::render('Player/Stats', [
                'error' => 'No player profile found for your account.',
                'seasons' => []
            ]);
        }
        
        // Get available seasons
        $seasons = Season::orderBy('start_date', 'desc')->get();
        $selectedSeasonId = $request->input('season_id', $seasons->first()->id ?? null);
        
        // Get player's team
        $team = $player->teams()
            ->whereHas('season', function ($query) use ($selectedSeasonId) {
                $query->where('id', $selectedSeasonId);
            })
            ->first();
            
        // Get player's game stats for the selected season
        $gameStats = PlayerGameStat::with(['game', 'game.homeTeam', 'game.awayTeam'])
            ->where('player_id', $player->id)
            ->whereHas('game.homeTeam.season', function ($query) use ($selectedSeasonId) {
                $query->where('id', $selectedSeasonId);
            })
            ->orWhereHas('game.awayTeam.season', function ($query) use ($selectedSeasonId, $player) {
                $query->where('id', $selectedSeasonId)
                      ->where('player_game_stats.player_id', $player->id);
            })
            ->get();
            
        // Summary stats calculation
        $totalGames = $gameStats->count();
        $totalGoals = $gameStats->sum('goals');
        $totalAssists = $gameStats->sum('assists');
        $totalPoints = $totalGoals + $totalAssists;
        $totalPenalties = $gameStats->sum('penalties');
        
        // Format game stats for display
        $formattedGameStats = $gameStats->map(function ($stat) use ($player) {
            $game = $stat->game;
            $isHomeTeam = $game->homeTeam->players->contains('id', $player->id);
            $opposingTeam = $isHomeTeam ? $game->awayTeam : $game->homeTeam;
            $result = '';
            
            if ($game->home_score !== null && $game->away_score !== null) {
                if ($isHomeTeam) {
                    $result = $game->home_score > $game->away_score ? 'W' : ($game->home_score < $game->away_score ? 'L' : 'T');
                } else {
                    $result = $game->away_score > $game->home_score ? 'W' : ($game->away_score < $game->home_score ? 'L' : 'T');
                }
                $result .= ' ' . $game->home_score . '-' . $game->away_score;
            }
            
            return [
                'id' => $game->id,
                'date' => $game->scheduled_date,
                'opponent' => $opposingTeam->name,
                'result' => $result,
                'goals' => $stat->goals,
                'assists' => $stat->assists,
                'penalties' => $stat->penalties,
            ];
        });
        
        return Inertia::render('Player/Stats', [
            'playerStats' => [
                'summary' => [
                    'name' => $player->first_name . ' ' . $player->last_name,
                    'jerseyNumber' => $player->jersey_number,
                    'position' => $player->position,
                    'gamesPlayed' => $totalGames,
                    'goals' => $totalGoals,
                    'assists' => $totalAssists, 
                    'penalties' => $totalPenalties,
                    'totalPoints' => $totalPoints
                ],
                'gameStats' => $formattedGameStats,
                'seasons' => $seasons->map(function ($season) {
                    return [
                        'id' => $season->id,
                        'name' => $season->name,
                    ];
                }),
                'selectedSeasonId' => $selectedSeasonId
            ]
        ]);
    }
} 