<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerGameStat extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'game_id',
        'player_id',
        'team_id',
        'goals',
        'assists',
        'shots_on_goal',
        'saves',
        'goals_against',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'goals' => 'integer',
        'assists' => 'integer',
        'shots_on_goal' => 'integer',
        'saves' => 'integer',
        'goals_against' => 'integer',
    ];

    /**
     * Get the game the stats belong to.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Get the player the stats belong to.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * Get the team the player was playing for when these stats were recorded.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }
}
