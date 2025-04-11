<?php

namespace App\Http\Controllers;

use App\Constants\Filters;
use App\Models\Game;
use App\Models\League;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class GameController extends Controller
{
    /**
     * Display a listing of the games.
     */
    public function index(Request $request)
    {
        // Log the start of the method for debugging
        Log::info('GameController@index started', ['request' => $request->all()]);

        // Get filter parameters from the request
        $leagueName = $request->input(Filters::LEAGUE);
        $seasonName = $request->input(Filters::SEASON);
        $teamId = $request->input(Filters::TEAM);
        $status = $request->input(Filters::STATUS);
        
        // Query leagues, seasons and teams for filters - ensure uniqueness
        $leagues = League::select('id', 'name')->distinct()->orderBy('name')->get();
        $seasons = Season::select('id', 'name', 'start_date')->distinct()->orderBy('start_date', 'desc')->get();
        $teams = Team::select('id', 'name')->distinct()->orderBy('name')->get();
        
        // Log filter entities
        Log::info('Filter entities loaded', [
            'leagues_count' => $leagues->count(),
            'seasons_count' => $seasons->count(),
            'teams_count' => $teams->count(),
        ]);
        
        // Base query for games with necessary relationships
        $gamesQuery = Game::with(['league', 'season', 'homeTeam', 'awayTeam']);
        
        // Apply filters if provided
        if ($leagueName) {
            $gamesQuery->whereHas('league', function($query) use ($leagueName) {
                $query->where('name', $leagueName);
            });
        }
        
        if ($seasonName) {
            $gamesQuery->whereHas('season', function($query) use ($seasonName) {
                $query->where('name', $seasonName);
            });
        }
        
        if ($teamId) {
            $gamesQuery->where(function($query) use ($teamId) {
                $query->where('home_team_id', $teamId)
                      ->orWhere('away_team_id', $teamId);
            });
        }
        
        if ($status) {
            $gamesQuery->where(Filters::STATUS, $status);
        }
        
        // Get games sorted by date (most recent first)
        $games = $gamesQuery->orderBy('game_date_time', 'desc')
                           ->paginate(15)
                           ->withQueryString();
        
        // Log the games query results
        Log::info('Games query executed', [
            'games_count' => $games->count(),
            'total_pages' => $games->lastPage(),
            'current_page' => $games->currentPage(),
        ]);
        
        $response = [
            'games' => $games,
            'leagues' => $leagues,
            'seasons' => $seasons,
            'teams' => $teams,
            'statusOptions' => Filters::getStatusOptions(),
            'filters' => [
                Filters::LEAGUE => $leagueName,
                Filters::SEASON => $seasonName,
                Filters::TEAM => $teamId,
                Filters::STATUS => $status,
            ],
        ];
        
        // Log the final response structure (excluding large data)
        Log::info('GameController@index response prepared', [
            'games_count' => $games->count(),
            'leagues_count' => $leagues->count(),
            'seasons_count' => $seasons->count(),
            'teams_count' => $teams->count(),
        ]);
        
        return Inertia::render('Games/Index', $response);
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