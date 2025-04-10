<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class GamePenalty extends Model
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
        'penalty_code_id',
        'period',
        'time_off_clock',
    ];

    /**
     * Get the game the penalty occurred in.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Get the player who received the penalty.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class);
    }

    /**
     * Get the team associated with the penalty.
     */
    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Get the penalty code for this penalty.
     */
    public function penaltyCode(): BelongsTo
    {
        return $this->belongsTo(PenaltyCode::class);
    }
}
