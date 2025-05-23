<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;

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

    /**
     * Scope a query to only include games that belong to a specific team and have a video URL.
     *
     * @param  \Illuminate\Database\Eloquent\Builder  $query
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeTeamGamesWithVideo(Builder $query, Team $team): Builder
    {
        Log::info('Game::scopeTeamGamesWithVideo - Team: ', ['team_id' => $team->id, 'team_name' => $team->name]);
        
        try {
            // Check if the video_url column exists
            if (Schema::hasColumn('games', 'video_url')) {
                Log::info('Using video_url column');
                
                return $query->where(function($query) use ($team) {
                        $query->where('home_team_id', $team->id)
                              ->orWhere('away_team_id', $team->id);
                    })
                    ->whereNotNull('video_url')
                    ->orderBy('game_date_time', 'desc');
            } else {
                // If the column doesn't exist, log all columns
                $columns = Schema::getColumnListing('games');
                Log::warning('video_url column not found. Available columns:', $columns);
                
                // Check alternate column names
                $videoColumns = ['video_link', 'video_uri', 'video'];
                $foundColumn = null;
                
                foreach ($videoColumns as $column) {
                    if (Schema::hasColumn('games', $column)) {
                        $foundColumn = $column;
                        Log::info("Found alternative video column: $column");
                        break;
                    }
                }
                
                if ($foundColumn) {
                    return $query->where(function($query) use ($team) {
                            $query->where('home_team_id', $team->id)
                                  ->orWhere('away_team_id', $team->id);
                        })
                        ->whereNotNull($foundColumn)
                        ->orderBy('game_date_time', 'desc');
                } else {
                    // If no video column found, just return team's games
                    Log::warning('No video column found. Returning all team games.');
                    return $query->where(function($query) use ($team) {
                            $query->where('home_team_id', $team->id)
                                  ->orWhere('away_team_id', $team->id);
                        })
                        ->orderBy('game_date_time', 'desc');
                }
            }
        } catch (\Exception $e) {
            Log::error('Error in scopeTeamGamesWithVideo: ' . $e->getMessage());
            
            // Fallback to basic query
            return $query->where(function($query) use ($team) {
                    $query->where('home_team_id', $team->id)
                          ->orWhere('away_team_id', $team->id);
                })
                ->orderBy('game_date_time', 'desc');
        }
    }

    /**
     * Get games with videos for a specific team.
     *
     * @param  \App\Models\Team  $team
     * @param  int  $limit
     * @param  array  $with
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public static function getTeamGamesWithVideo(Team $team, int $limit = 10, array $with = ['homeTeam', 'awayTeam']): \Illuminate\Database\Eloquent\Collection
    {
        return static::teamGamesWithVideo($team)
            ->with($with)
            ->limit($limit)
            ->get();
    }
}
