<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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
        'title',        
        'description',
        'video_url',
        'start_time',
        'end_time',
        // Legacy column names for compatibility
        'clip_title',
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
        'start_time' => 'float',
        'end_time' => 'float',
        'start_time_seconds' => 'float',
        'end_time_seconds' => 'float',
    ];

    /**
     * Boot the model.
     */
    protected static function boot()
    {
        parent::boot();

        static::saving(function ($clip) {
            // Check if we're trying to access columns that don't exist and make adjustments
            try {
                $columns = Schema::getColumnListing('clips');
                
                // Handle title/clip_title discrepancy
                if (!in_array('title', $columns) && in_array('clip_title', $columns) && isset($clip->title)) {
                    $clip->clip_title = $clip->title;
                    unset($clip->title);
                }
                
                // Handle description/notes discrepancy
                if (!in_array('description', $columns) && in_array('notes', $columns) && isset($clip->description)) {
                    $clip->notes = $clip->description;
                    unset($clip->description);
                }
                
                // Handle start_time/start_time_seconds discrepancy
                if (!in_array('start_time', $columns) && in_array('start_time_seconds', $columns) && isset($clip->start_time)) {
                    $clip->start_time_seconds = $clip->start_time;
                    unset($clip->start_time);
                }
                
                // Handle end_time/end_time_seconds discrepancy
                if (!in_array('end_time', $columns) && in_array('end_time_seconds', $columns) && isset($clip->end_time)) {
                    $clip->end_time_seconds = $clip->end_time;
                    unset($clip->end_time);
                }
            } catch (\Exception $e) {
                Log::error('Error checking clip columns: ' . $e->getMessage());
                // Continue anyway and let the database handle it
            }
        });
    }

    /**
     * Get title with fallback to clip_title
     */
    public function getTitleAttribute($value)
    {
        if ($value) {
            return $value;
        }
        
        return $this->attributes['clip_title'] ?? null;
    }
    
    /**
     * Get description with fallback to notes
     */
    public function getDescriptionAttribute($value)
    {
        if ($value) {
            return $value;
        }
        
        return $this->attributes['notes'] ?? null;
    }
    
    /**
     * Get start_time with fallback to start_time_seconds
     */
    public function getStartTimeAttribute($value)
    {
        if ($value !== null) {
            return $value;
        }
        
        return $this->attributes['start_time_seconds'] ?? null;
    }
    
    /**
     * Get end_time with fallback to end_time_seconds
     */
    public function getEndTimeAttribute($value)
    {
        if ($value !== null) {
            return $value;
        }
        
        return $this->attributes['end_time_seconds'] ?? null;
    }

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
    
    /**
     * Alias for sharedWithPlayers relationship for backward compatibility.
     */
    public function players(): BelongsToMany
    {
        return $this->sharedWithPlayers();
    }
}
