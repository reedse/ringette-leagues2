<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'user_id',
        'first_name',
        'last_name',
        'jersey_number',
        'position',
        'date_of_birth',
    ];

    /**
     * Get the user account associated with the player.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the teams the player belongs to (via roster entries).
     */
    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'roster_entries')
            ->withPivot('season_id')
            ->withTimestamps()
            ->using(RosterEntry::class);
    }

    /**
     * Get the seasons the player has played in (via roster entries).
     */
    public function seasons(): BelongsToMany
    {
        return $this->belongsToMany(Season::class, 'roster_entries')
            ->withPivot('team_id')
            ->withTimestamps()
            ->using(RosterEntry::class);
    }

    /**
     * Get the roster entries for the player.
     */
    public function rosterEntries(): HasMany
    {
        return $this->hasMany(RosterEntry::class);
    }

    /**
     * Get the game statistics for the player.
     */
    public function gameStats(): HasMany
    {
        return $this->hasMany(PlayerGameStat::class);
    }

    /**
     * Get the penalties assessed to the player.
     */
    public function penalties(): HasMany
    {
        return $this->hasMany(GamePenalty::class);
    }

    /**
     * Get the clips shared with the player.
     */
    public function sharedClips(): BelongsToMany
    {
        return $this->belongsToMany(Clip::class, 'clip_player')->withPivot('coach_note', 'sent_at')->withTimestamps()->using(ClipPlayer::class);
    }
}
