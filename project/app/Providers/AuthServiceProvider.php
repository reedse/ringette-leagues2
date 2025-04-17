<?php

namespace App\Providers;

use App\Models\Clip;
use App\Models\Game;
use App\Models\League;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use App\Policies\ClipPolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The model to policy mappings for the application.
     *
     * @var array<class-string, class-string>
     */
    protected $policies = [
        Clip::class => ClipPolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        // Define role-based Gates
        Gate::define('access-admin', function (User $user) {
            return $user->isCoach() || $user->isLeagueAdmin();
        });

        Gate::define('manage-teams', function (User $user) {
            return $user->isLeagueAdmin();
        });

        Gate::define('manage-leagues', function (User $user) {
            return $user->isLeagueAdmin();
        });

        Gate::define('manage-team', function (User $user, Team $team) {
            return $user->isLeagueAdmin() || ($user->isCoach() && $user->managed_team_id === $team->id);
        });

        Gate::define('manage-game', function (User $user, Game $game) {
            return $user->isLeagueAdmin() || 
                   ($user->isCoach() && ($user->managed_team_id === $game->home_team_id || 
                                        $user->managed_team_id === $game->away_team_id));
        });

        Gate::define('manage-players', function (User $user, Team $team = null) {
            if ($user->isLeagueAdmin()) {
                return true;
            }
            
            // Coach can only manage players in their team
            if ($team) {
                return $user->isCoach() && $user->managed_team_id === $team->id;
            }
            
            return $user->isCoach();
        });

        Gate::define('manage-clips', function (User $user, Clip $clip = null) {
            if ($user->isLeagueAdmin()) {
                return true;
            }
            
            // For existing clips, check if user is the creator
            if ($clip) {
                return $user->isCoach() && $user->id === $clip->coach_user_id;
            }
            
            return $user->isCoach();
        });

        Gate::define('view-clip', function (User $user, Clip $clip) {
            // League admins and the coach who created the clip can always view it
            if ($user->isLeagueAdmin() || ($user->isCoach() && $user->id === $clip->coach_user_id)) {
                return true;
            }
            
            // Players can only view clips that have been shared with them
            if ($user->isPlayer() && $user->player) {
                return $clip->players()->where('player_id', $user->player->id)->exists();
            }
            
            return false;
        });

        Gate::define('access-player-profile', function (User $user, Player $player) {
            // Allow access to own profile, admins, and coaches of the player's team
            return $user->isLeagueAdmin() || 
                   ($user->player && $user->player->id === $player->id) ||
                   ($user->isCoach() && $player->teams()->where('team_id', $user->managed_team_id)->exists());
        });

        // Define role-based permissions
        Gate::define('access-player-features', function ($user) {
            return $user->hasRole('player');
        });

        Gate::define('access-coach-features', function ($user) {
            return $user->hasRole('coach');
        });

        Gate::define('access-admin-features', function ($user) {
            return $user->hasRole('league_admin');
        });

        // Define subscription-based permissions
        Gate::define('view-clips', function ($user) {
            return $user->hasRole('coach') || 
                  ($user->hasRole('player') && $user->subscribed('clips'));
        });
    }
} 