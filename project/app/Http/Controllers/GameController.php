<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\League;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Http\Request;
use Inertia\Inertia;

class GameController extends Controller
{
    /**
     * Display a listing of the games.
     */
    public function index(Request $request)
    {
        // Get filter parameters from the request
        $leagueId = $request->input('league');
        $seasonId = $request->input('season');
        $teamId = $request->input('team');
        $status = $request->input('status');
        
        // Query leagues, seasons and teams for filters
        $leagues = League::orderBy('name')->get();
        $seasons = Season::orderBy('start_date', 'desc')->get();
        $teams = Team::orderBy('name')->get();
        
        // Base query for games with necessary relationships
        $gamesQuery = Game::with(['league', 'season', 'homeTeam', 'awayTeam']);
        
        // Apply filters if provided
        if ($leagueId) {
            $gamesQuery->where('league_id', $leagueId);
        }
        
        if ($seasonId) {
            $gamesQuery->where('season_id', $seasonId);
        }
        
        if ($teamId) {
            $gamesQuery->where(function($query) use ($teamId) {
                $query->where('home_team_id', $teamId)
                      ->orWhere('away_team_id', $teamId);
            });
        }
        
        if ($status) {
            $gamesQuery->where('status', $status);
        }
        
        // Get games sorted by date (most recent first)
        $games = $gamesQuery->orderBy('game_date_time', 'desc')
                           ->paginate(15)
                           ->withQueryString();
        
        return Inertia::render('Games/Index', [
            'games' => $games,
            'leagues' => $leagues,
            'seasons' => $seasons,
            'teams' => $teams,
            'statusOptions' => ['Scheduled', 'In Progress', 'Completed'],
            'filters' => [
                'league' => $leagueId,
                'season' => $seasonId,
                'team' => $teamId,
                'status' => $status,
            ],
        ]);
    }
    
    /**
     * Display the specified game.
     */
    public function show(Game $game)
    {
        // Load necessary relationships
        $game->load([
            'league', 
            'season', 
            'homeTeam', 
            'awayTeam',
            'playerStats' => function($query) {
                $query->with('player');
            },
            'penalties' => function($query) {
                $query->with(['player', 'penaltyCode']);
            },
            'clips'
        ]);
        
        return Inertia::render('Games/Show', [
            'game' => $game,
        ]);
    }
} 