<?php

namespace App\Http\Controllers;

use App\Models\Player;
use App\Models\RosterEntry;
use App\Models\Season;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PlayerTeamController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $player = Player::where('user_id', $user->id)->first();
        
        if (!$player) {
            return Inertia::render('Player/Team', [
                'error' => 'No player profile found for your account.',
                'seasons' => []
            ]);
        }
        
        // Get available seasons
        $seasons = Season::orderBy('start_date', 'desc')->get();
        $selectedSeasonId = $request->input('season_id', $seasons->first()->id ?? null);
        
        // Get player's team for the selected season
        $team = $player->teams()
            ->whereHas('season', function ($query) use ($selectedSeasonId) {
                $query->where('id', $selectedSeasonId);
            })
            ->first();
            
        if (!$team) {
            return Inertia::render('Player/Team', [
                'error' => 'You are not on a team for the selected season.',
                'seasons' => $seasons->map(function ($season) {
                    return [
                        'id' => $season->id,
                        'name' => $season->name,
                    ];
                }),
                'selectedSeasonId' => $selectedSeasonId
            ]);
        }
        
        // Get the team's roster
        $roster = RosterEntry::with(['player'])
            ->where('team_id', $team->id)
            ->get();
            
        // Get the team's upcoming games
        $upcomingGames = $team->homeGames()
            ->where('scheduled_date', '>=', now())
            ->where('is_complete', false)
            ->with(['homeTeam', 'awayTeam'])
            ->union(
                $team->awayGames()
                    ->where('scheduled_date', '>=', now())
                    ->where('is_complete', false)
                    ->with(['homeTeam', 'awayTeam'])
            )
            ->orderBy('scheduled_date')
            ->take(5)
            ->get();
            
        // Get the team's coach
        $coach = $team->coach;
        
        // Get team statistics
        $teamStats = [
            'wins' => 0,
            'losses' => 0,
            'ties' => 0,
            'goalsFor' => 0,
            'goalsAgainst' => 0,
        ];
        
        // Process home games
        foreach ($team->homeGames()->where('is_complete', true)->get() as $game) {
            if ($game->home_score > $game->away_score) {
                $teamStats['wins']++;
            } elseif ($game->home_score < $game->away_score) {
                $teamStats['losses']++;
            } else {
                $teamStats['ties']++;
            }
            $teamStats['goalsFor'] += $game->home_score;
            $teamStats['goalsAgainst'] += $game->away_score;
        }
        
        // Process away games
        foreach ($team->awayGames()->where('is_complete', true)->get() as $game) {
            if ($game->away_score > $game->home_score) {
                $teamStats['wins']++;
            } elseif ($game->away_score < $game->home_score) {
                $teamStats['losses']++;
            } else {
                $teamStats['ties']++;
            }
            $teamStats['goalsFor'] += $game->away_score;
            $teamStats['goalsAgainst'] += $game->home_score;
        }
        
        return Inertia::render('Player/Team', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
                'league' => $team->league->name,
                'association' => $team->league->association->name,
                'season' => $team->season->name,
                'coach' => $coach ? [
                    'name' => $coach->name,
                    'email' => $coach->email
                ] : null,
                'stats' => $teamStats,
                'record' => $teamStats['wins'] . '-' . $teamStats['losses'] . '-' . $teamStats['ties'],
            ],
            'roster' => $roster->map(function ($entry) {
                return [
                    'id' => $entry->player->id,
                    'name' => $entry->player->first_name . ' ' . $entry->player->last_name,
                    'jerseyNumber' => $entry->player->jersey_number,
                    'position' => $entry->player->position,
                ];
            })->sortBy('jerseyNumber')->values(),
            'upcomingGames' => $upcomingGames->map(function ($game) use ($team) {
                $isHomeTeam = $game->homeTeam->id === $team->id;
                $opponent = $isHomeTeam ? $game->awayTeam : $game->homeTeam;
                
                return [
                    'id' => $game->id,
                    'date' => $game->scheduled_date,
                    'time' => $game->scheduled_time,
                    'location' => $game->location,
                    'opponent' => $opponent->name,
                    'isHome' => $isHomeTeam,
                ];
            }),
            'seasons' => $seasons->map(function ($season) {
                return [
                    'id' => $season->id,
                    'name' => $season->name,
                ];
            }),
            'selectedSeasonId' => $selectedSeasonId
        ]);
    }
} 