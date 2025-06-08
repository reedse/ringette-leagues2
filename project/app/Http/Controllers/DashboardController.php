<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Display the dashboard view based on user role.
     */
    public function index(Request $request)
    {
        $user = $request->user();
        $dashboardData = [];

        // Determine primary role for dashboard display (userRole is now shared globally)
        if ($user->isLeagueAdmin()) {
            $dashboardData = $this->getLeagueAdminData($user);
        } elseif ($user->isCoach()) {
            $dashboardData = $this->getCoachData($user);
        } elseif ($user->isPlayer()) {
            $dashboardData = $this->getPlayerData($user);
        }

        return Inertia::render('Dashboard', [
            'dashboardData' => $dashboardData,
        ]);
    }

    /**
     * Get dashboard data for league admin users.
     */
    private function getLeagueAdminData($user)
    {
        return [
            'associations_count' => \App\Models\Association::count(),
            'leagues_count' => \App\Models\League::count(),
            'teams_count' => \App\Models\Team::count(),
            'players_count' => \App\Models\Player::count(),
            'games_count' => \App\Models\Game::where('status', 'completed')->count(),
            'upcoming_games_count' => \App\Models\Game::where('status', 'scheduled')->count(),
        ];
    }

    /**
     * Get dashboard data for coach users.
     */
    private function getCoachData($user)
    {
        $teamId = $user->managed_team_id;
        
        if (!$teamId) {
            return ['no_team' => true];
        }
        
        $team = \App\Models\Team::find($teamId);
        
        return [
            'team' => $team,
            'players_count' => $team->players()->count(),
            'games' => \App\Models\Game::where(function($query) use ($teamId) {
                $query->where('home_team_id', $teamId)
                    ->orWhere('away_team_id', $teamId);
            })
            ->orderBy('game_date_time')
            ->take(5)
            ->get(),
            'clips_count' => \App\Models\Clip::where('coach_user_id', $user->id)->count(),
        ];
    }

    /**
     * Get dashboard data for player users.
     */
    private function getPlayerData($user)
    {
        $player = $user->player;
        
        if (!$player) {
            return ['no_player_profile' => true];
        }
        
        $teamIds = $player->teams()->pluck('team_id')->toArray();
        
        return [
            'player' => $player,
            'teams' => \App\Models\Team::whereIn('id', $teamIds)->get(),
            'games' => \App\Models\Game::whereIn('home_team_id', $teamIds)
                ->orWhereIn('away_team_id', $teamIds)
                ->orderBy('game_date_time')
                ->take(5)
                ->get(),
            'stats' => \App\Models\PlayerGameStat::where('player_id', $player->id)
                ->get(),
            'clips_count' => $player->sharedClips()->count(),
        ];
    }
}
