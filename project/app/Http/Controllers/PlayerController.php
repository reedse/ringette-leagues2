<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\Game;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Http\Request;
use Inertia\Inertia;

class PlayerController extends Controller
{
    /**
     * Display a listing of the players.
     */
    public function index(Request $request)
    {
        // Get filter parameters from the request
        $teamId = $request->input('team');
        $seasonId = $request->input('season');
        $position = $request->input('position');
        
        // Query teams and seasons for filters
        $teams = Team::orderBy('name')->get();
        $seasons = Season::orderBy('start_date', 'desc')->get();
        
        // Base query for players
        $playersQuery = Player::with(['user', 'teams', 'seasons']);
        
        // Apply filters if provided
        if ($teamId) {
            $playersQuery->whereHas('teams', function($query) use ($teamId) {
                $query->where('teams.id', $teamId);
            });
        }
        
        if ($seasonId) {
            $playersQuery->whereHas('seasons', function($query) use ($seasonId) {
                $query->where('seasons.id', $seasonId);
            });
        }
        
        if ($position) {
            $playersQuery->where('position', $position);
        }
        
        // Get players sorted by name
        $players = $playersQuery->orderBy('last_name')
                               ->orderBy('first_name')
                               ->paginate(20)
                               ->withQueryString();
        
        return Inertia::render('Players/Index', [
            'players' => $players,
            'teams' => $teams,
            'seasons' => $seasons,
            'positionOptions' => ['Forward', 'Defense', 'Goalie'],
            'filters' => [
                'team' => $teamId,
                'season' => $seasonId,
                'position' => $position,
            ],
        ]);
    }
    
    /**
     * Display the specified player profile.
     */
    public function show(Player $player)
    {
        // Load the player with all their relationships
        $player->load([
            'user', 
            'teams' => function($query) {
                $query->with('league', 'season');
            },
            'rosterEntries' => function($query) {
                $query->with('team', 'season');
            },
            'gameStats' => function($query) {
                $query->with('game');
            },
            'penalties' => function($query) {
                $query->with('game', 'penaltyCode');
            },
            'sharedClips'
        ]);
        
        // Calculate cumulative stats from all games
        $cumulativeStats = [
            'games' => $player->gameStats->count(),
            'goals' => $player->gameStats->sum('goals'),
            'assists' => $player->gameStats->sum('assists'),
            'points' => $player->gameStats->sum('goals') + $player->gameStats->sum('assists'),
            'shots' => $player->gameStats->sum('shots'),
            'plus_minus' => $player->gameStats->sum('plus_minus'),
            'penalties' => $player->penalties->count(),
            'penalty_minutes' => $player->penalties->sum('minutes'),
        ];
        
        // Calculate shooting percentage if shots > 0
        if ($cumulativeStats['shots'] > 0) {
            $cumulativeStats['shooting_percentage'] = round(($cumulativeStats['goals'] / $cumulativeStats['shots']) * 100, 1);
        } else {
            $cumulativeStats['shooting_percentage'] = 0;
        }
        
        // Get recent games with player stats
        $recentGames = Game::whereHas('playerStats', function($query) use ($player) {
                $query->where('player_id', $player->id);
            })
            ->with([
                'league',
                'season',
                'homeTeam',
                'awayTeam',
                'playerStats' => function($query) use ($player) {
                    $query->where('player_id', $player->id);
                }
            ])
            ->orderBy('game_date_time', 'desc')
            ->take(5)
            ->get();
        
        // Current teams (active roster entries)
        $currentTeam = $player->teams()->latest()->first();
        
        return Inertia::render('Players/Show', [
            'player' => $player,
            'cumulativeStats' => $cumulativeStats,
            'recentGames' => $recentGames,
            'currentTeam' => $currentTeam,
        ]);
    }
}
