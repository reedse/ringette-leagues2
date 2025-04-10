<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Player;
use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class PlayerScheduleController extends Controller
{
    public function index(Request $request)
    {
        $user = Auth::user();
        $player = Player::where('user_id', $user->id)->first();
        
        if (!$player) {
            return Inertia::render('Player/Schedule', [
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
            return Inertia::render('Player/Schedule', [
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
        
        // Get upcoming games
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
            ->get();
            
        // Get past games
        $pastGames = $team->homeGames()
            ->where(function ($query) {
                $query->where('scheduled_date', '<', now())
                    ->orWhere('is_complete', true);
            })
            ->with(['homeTeam', 'awayTeam'])
            ->union(
                $team->awayGames()
                    ->where(function ($query) {
                        $query->where('scheduled_date', '<', now())
                            ->orWhere('is_complete', true);
                    })
                    ->with(['homeTeam', 'awayTeam'])
            )
            ->orderBy('scheduled_date', 'desc')
            ->get();
            
        return Inertia::render('Player/Schedule', [
            'team' => [
                'id' => $team->id,
                'name' => $team->name,
            ],
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
            'pastGames' => $pastGames->map(function ($game) use ($team) {
                $isHomeTeam = $game->homeTeam->id === $team->id;
                $opponent = $isHomeTeam ? $game->awayTeam : $game->homeTeam;
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
                    'time' => $game->scheduled_time,
                    'location' => $game->location,
                    'opponent' => $opponent->name,
                    'isHome' => $isHomeTeam,
                    'result' => $result,
                    'isComplete' => $game->is_complete,
                ];
            }),
            'seasons' => $seasons->map(function ($season) {
                return [
                    'id' => $season->id,
                    'name' => $season->name,
                ];
            }),
            'selectedSeasonId' => $selectedSeasonId,
            'currentWeek' => now()->startOfWeek()->format('Y-m-d')
        ]);
    }
} 