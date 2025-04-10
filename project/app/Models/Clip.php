<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Clip extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'game_id',
        'coach_user_id',
        'clip_title',
        'video_url',
        'start_time_seconds',
        'end_time_seconds',
        'notes',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_time_seconds' => 'integer',
        'end_time_seconds' => 'integer',
    ];

    /**
     * Get the game the clip belongs to.
     */
    public function game(): BelongsTo
    {
        return $this->belongsTo(Game::class);
    }

    /**
     * Get the coach (user) who created the clip.
     */
    public function coach(): BelongsTo
    {
        return $this->belongsTo(User::class, 'coach_user_id');
    }

    /**
     * Get the players this clip has been shared with.
     */
    public function sharedWithPlayers(): BelongsToMany
    {
        return $this->belongsToMany(Player::class, 'clip_player')->withPivot('coach_note', 'sent_at')->withTimestamps()->using(ClipPlayer::class);
    }
}
