<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Game extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'season_id',
        'league_id',
        'home_team_id',
        'away_team_id',
        'game_date_time',
        'location',
        'home_score',
        'away_score',
        'status',
        'video_url',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'game_date_time' => 'datetime',
    ];

    /**
     * Get the season the game belongs to.
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(Season::class);
    }

    /**
     * Get the league the game belongs to.
     */
    public function league(): BelongsTo
    {
        return $this->belongsTo(League::class);
    }

    /**
     * Get the home team for the game.
     */
    public function homeTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'home_team_id');
    }

    /**
     * Get the away team for the game.
     */
    public function awayTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'away_team_id');
    }

    /**
     * Get the game statistics for this game.
     */
    public function playerStats(): HasMany
    {
        return $this->hasMany(PlayerGameStat::class);
    }

    /**
     * Get the penalties assessed during this game.
     */
    public function penalties(): HasMany
    {
        return $this->hasMany(GamePenalty::class);
    }

    /**
     * Get the clips associated with this game.
     */
    public function clips(): HasMany
    {
        return $this->hasMany(Clip::class);
    }
}
