<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\League;
use App\Models\Season;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Log;

class TeamController extends Controller
{
    /**
     * Display a listing of the teams.
     */
    public function index(Request $request)
    {
        // Log the start of the method for debugging
        Log::info('TeamController@index started', ['request' => $request->all()]);

        // Get filter parameters from the request
        $leagueId = $request->input('league');
        $seasonId = $request->input('season');
        
        // Query leagues and seasons for filters
        $leagues = League::orderBy('name')->get();
        $seasons = Season::orderBy('start_date', 'desc')->get();
        
        // Log filter entities
        Log::info('Filter entities loaded', [
            'leagues_count' => $leagues->count(),
            'seasons_count' => $seasons->count(),
        ]);
        
        // Base query for teams with necessary relationships
        $teamsQuery = Team::with(['league', 'season', 'association']);
        
        // Apply filters if provided
        if ($leagueId) {
            $teamsQuery->where('league_id', $leagueId);
        }
        
        if ($seasonId) {
            $teamsQuery->where('season_id', $seasonId);
        }
        
        // Get teams sorted by league and name
        $teams = $teamsQuery->orderBy('league_id')
                           ->orderBy('name')
                           ->paginate(20)
                           ->withQueryString();
        
        // Log the teams query results
        Log::info('Teams query executed', [
            'teams_count' => $teams->count(),
            'total_pages' => $teams->lastPage(),
            'current_page' => $teams->currentPage(),
        ]);
        
        $response = [
            'teams' => $teams,
            'leagues' => $leagues,
            'seasons' => $seasons,
            'filters' => [
                'league' => $leagueId,
                'season' => $seasonId,
            ],
        ];
        
        // Log the final response structure (excluding large data)
        Log::info('TeamController@index response prepared', [
            'teams_count' => $teams->count(),
            'leagues_count' => $leagues->count(),
            'seasons_count' => $seasons->count(),
        ]);
        
        return Inertia::render('Teams/Index', $response);
    }
    
    /**
     * Display the specified team.
     */
    public function show(Team $team)
    {
        // Load necessary relationships
        $team->load([
            'league', 
            'season', 
            'association',
            'players' => function($query) {
                $query->orderBy('jersey_number');
            },
            'homeGames' => function($query) {
                $query->with(['awayTeam', 'league', 'season'])
                     ->orderBy('game_date_time', 'desc')
                     ->take(5);
            },
            'awayGames' => function($query) {
                $query->with(['homeTeam', 'league', 'season'])
                     ->orderBy('game_date_time', 'desc')
                     ->take(5);
            }
        ]);
        
        // Get team record (wins, losses, etc.)
        $record = $this->getTeamRecord($team);
        
        // Get team statistics
        $stats = $this->getTeamStats($team);
        
        return Inertia::render('Teams/Show', [
            'team' => $team,
            'record' => $record,
            'stats' => $stats,
            'recentGames' => $this->getRecentGames($team),
        ]);
    }
    
    /**
     * Calculate team's win/loss record
     */
    private function getTeamRecord(Team $team)
    {
        $homeGames = $team->homeGames()->whereNotNull('home_score')->whereNotNull('away_score')->get();
        $awayGames = $team->awayGames()->whereNotNull('home_score')->whereNotNull('away_score')->get();
        
        $wins = 0;
        $losses = 0;
        $ties = 0;
        
        foreach ($homeGames as $game) {
            if ($game->home_score > $game->away_score) {
                $wins++;
            } elseif ($game->home_score < $game->away_score) {
                $losses++;
            } else {
                $ties++;
            }
        }
        
        foreach ($awayGames as $game) {
            if ($game->away_score > $game->home_score) {
                $wins++;
            } elseif ($game->away_score < $game->home_score) {
                $losses++;
            } else {
                $ties++;
            }
        }
        
        return [
            'wins' => $wins,
            'losses' => $losses,
            'ties' => $ties,
            'total' => $wins + $losses + $ties,
            'winPercentage' => ($wins + $losses + $ties) > 0 
                ? round($wins / ($wins + $losses + $ties) * 100, 1) 
                : 0,
        ];
    }
    
    /**
     * Get team statistics
     */
    private function getTeamStats(Team $team)
    {
        $homeGames = $team->homeGames()->whereNotNull('home_score')->whereNotNull('away_score')->get();
        $awayGames = $team->awayGames()->whereNotNull('home_score')->whereNotNull('away_score')->get();
        
        $goalsFor = 0;
        $goalsAgainst = 0;
        
        foreach ($homeGames as $game) {
            $goalsFor += $game->home_score;
            $goalsAgainst += $game->away_score;
        }
        
        foreach ($awayGames as $game) {
            $goalsFor += $game->away_score;
            $goalsAgainst += $game->home_score;
        }
        
        $gamesPlayed = count($homeGames) + count($awayGames);
        
        return [
            'goalsFor' => $goalsFor,
            'goalsAgainst' => $goalsAgainst,
            'goalDifferential' => $goalsFor - $goalsAgainst,
            'goalsForAvg' => $gamesPlayed > 0 ? round($goalsFor / $gamesPlayed, 2) : 0,
            'goalsAgainstAvg' => $gamesPlayed > 0 ? round($goalsAgainst / $gamesPlayed, 2) : 0,
        ];
    }
    
    /**
     * Get recent games for a team, combining home and away games
     */
    private function getRecentGames(Team $team)
    {
        $homeGames = $team->homeGames()
            ->with(['awayTeam', 'league', 'season'])
            ->orderBy('game_date_time', 'desc')
            ->take(5)
            ->get();
            
        $awayGames = $team->awayGames()
            ->with(['homeTeam', 'league', 'season'])
            ->orderBy('game_date_time', 'desc')
            ->take(5)
            ->get();
            
        return $homeGames->concat($awayGames)
            ->sortByDesc('game_date_time')
            ->take(5)
            ->values();
    }
}
