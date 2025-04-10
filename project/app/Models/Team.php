<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Team extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'association_id',
        'league_id',
        'season_id',
    ];

    /**
     * Get the association the team belongs to.
     */
    public function association(): BelongsTo
    {
        return $this->belongsTo(Association::class);
    }

    /**
     * Get the league the team belongs to.
     */
    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    /**
     * Get the primary season the team belongs to.
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * Get the players on the team (via roster entries).
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'roster_entries')
            ->withPivot('season_id')
            ->withTimestamps()
            ->using(RosterEntry::class);
    }

    /**
     * Get the seasons the team has participated in (via roster entries).
     */
    public function seasons(): BelongsToMany
    {
        return $this->belongsToMany(Season::class, 'roster_entries')
            ->withPivot('player_id')
            ->withTimestamps()
            ->using(RosterEntry::class);
    }

    /**
     * Get the roster entries for the team.
     */
    public function rosterEntries(): HasMany
    {
        return $this->hasMany(RosterEntry::class);
    }

    /**
     * Get the games where this team is the home team.
     */
    public function homeGames(): HasMany
    {
        return $this->hasMany(Game::class, 'home_team_id');
    }

    /**
     * Get the games where this team is the away team.
     */
    public function awayGames(): HasMany
    {
        return $this->hasMany(Game::class, 'away_team_id');
    }

    /**
     * Get all games involving this team (home or away).
     */
    public function games()
    {
        return $this->homeGames()->union($this->awayGames()->toBase());
        // Or you could query Game::where('home_team_id', $this->id)->orWhere('away_team_id', $this->id);
    }

    /**
     * Get the game statistics associated with this team.
     */
    public function gameStats(): HasMany
    {
        return $this->hasMany(PlayerGameStat::class);
    }

    /**
     * Get the penalties associated with this team.
     */
    public function penalties(): HasMany
    {
        return $this->hasMany(GamePenalty::class);
    }

     /**
      * Get the user managing this team (if any).
      */
     public function manager(): HasOne
     {
         // Note: The FK is on the users table
         return $this->hasOne(User::class, 'managed_team_id');
     }
}
