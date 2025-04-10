<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Season extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'start_date',
        'end_date',
        'league_id',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    /**
     * Get the league this season belongs to.
     */
    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    /**
     * Get the teams participating in this season (via roster entries).
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'roster_entries')
            ->withPivot('player_id')
            ->withTimestamps()
            ->using(RosterEntry::class);
    }

    /**
     * Get the players participating in this season (via roster entries).
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'roster_entries')
            ->withPivot('team_id')
            ->withTimestamps()
            ->using(RosterEntry::class);
    }

    /**
     * Get the roster entries for this season.
     */
    public function rosterEntries(): HasMany
    {
        return $this->hasMany(RosterEntry::class);
    }

    /**
     * Get the games scheduled for this season.
     */
    public function games(): HasMany
    {
        return $this->hasMany(Game::class);
    }
}
