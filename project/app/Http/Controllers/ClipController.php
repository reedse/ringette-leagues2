<?php

namespace App\Http\Controllers;

use App\Events\ClipShared;
use App\Models\Clip;
use App\Models\ClipPlayer;
use App\Models\Game;
use App\Models\Player;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class ClipController extends Controller
{
    /**
     * Display a listing of the clips.
     */
    public function index()
    {
        $user = Auth::user();
        $team = $user->managedTeam;
        
        if (!$team) {
            return Inertia::render('Coach/Clips', [
                'error' => 'You are not currently managing any team.',
                'clips' => [],
            ]);
        }

        // Get all clips created by the coach
        $clips = Clip::where('coach_user_id', $user->id)
            ->with(['game.homeTeam', 'game.awayTeam', 'players'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Coach/Clips', [
            'team' => $team,
            'clips' => $clips,
        ]);
    }

    /**
     * Show the form for creating a new clip.
     */
    public function create(Request $request)
    {
        $user = Auth::user();
        $team = $user->managedTeam;
        
        Log::info('ClipController@create - User ID: ' . $user->id);
        Log::info('ClipController@create - Team: ', ['team_id' => $team ? $team->id : null, 'team_name' => $team ? $team->name : null]);
        
        if (!$team) {
            return redirect()->route('coach.clips')
                ->with('error', 'You are not currently managing any team.');
        }

        $gameId = $request->query('game_id');
        $game = null;
        
        if ($gameId) {
            $game = Game::where('id', $gameId)
                ->where(function($query) use ($team) {
                    $query->where('home_team_id', $team->id)
                          ->orWhere('away_team_id', $team->id);
                })
                ->with(['homeTeam', 'awayTeam'])
                ->first();
                
            Log::info('ClipController@create - Game by ID: ', ['game_id' => $gameId, 'found' => !is_null($game)]);
        }
        
        if (!$game && $gameId) {
            return redirect()->route('coach.clips')
                ->with('error', 'Game not found or you do not have permission to view it.');
        }
        
        // If no game specified, get recent games for selection
        $recentGames = [];
        $debugMode = $request->has('debug_mode') || config('app.env') === 'local';
        
        if (!$game) {
            // Get a sample game to inspect schema
            $sampleGame = Game::first();
            if ($sampleGame) {
                Log::info('Sample game from database:', $sampleGame->toArray());
            } else {
                Log::warning('No games exist in the database!');
            }
            
            // Debug: Check team relationships exist
            $teamClass = get_class($team);
            Log::info("Team class: $teamClass, Team ID: {$team->id}");
            
            // Check if the team relationships exist in the database
            $homeTeamCount = Game::where('home_team_id', $team->id)->count();
            $awayTeamCount = Game::where('away_team_id', $team->id)->count();
            Log::info("Home team games: $homeTeamCount, Away team games: $awayTeamCount");
            
            // Get all team games with videos
            $query = Game::teamGamesWithVideo($team)->with(['homeTeam', 'awayTeam'])->limit(10);
            Log::info('ClipController@create - Games query: ' . $query->toSql(), $query->getBindings());
            
            $recentGames = $query->get();
            
            // Debug game structure
            if ($recentGames->isNotEmpty()) {
                $firstGame = $recentGames->first();
                Log::info('First game loaded:', [
                    'id' => $firstGame->id,
                    'home_team_id' => $firstGame->home_team_id,
                    'away_team_id' => $firstGame->away_team_id,
                    'has_homeTeam_relation' => $firstGame->relationLoaded('homeTeam'),
                    'has_awayTeam_relation' => $firstGame->relationLoaded('awayTeam'),
                    'homeTeam' => $firstGame->homeTeam ? [
                        'id' => $firstGame->homeTeam->id,
                        'name' => $firstGame->homeTeam->name
                    ] : null,
                    'awayTeam' => $firstGame->awayTeam ? [
                        'id' => $firstGame->awayTeam->id,
                        'name' => $firstGame->awayTeam->name
                    ] : null,
                ]);
                
                // If team relations not loaded properly, try to load them manually
                if (!$firstGame->homeTeam || !$firstGame->awayTeam) {
                    Log::warning('Team relations not loaded, attempting to manually load them');
                    
                    // Try to manually load the team relations
                    foreach ($recentGames as $gm) {
                        if (!$gm->homeTeam) {
                            $gm->setRelation('homeTeam', Team::find($gm->home_team_id));
                        }
                        if (!$gm->awayTeam) {
                            $gm->setRelation('awayTeam', Team::find($gm->away_team_id));
                        }
                    }
                    
                    // Verify the first game's relations now
                    $firstGame = $recentGames->first();
                    Log::info('After manual loading, first game relations:', [
                        'has_homeTeam_relation' => $firstGame->relationLoaded('homeTeam'),
                        'has_awayTeam_relation' => $firstGame->relationLoaded('awayTeam'),
                        'homeTeam' => $firstGame->homeTeam ? [
                            'id' => $firstGame->homeTeam->id,
                            'name' => $firstGame->homeTeam->name
                        ] : null,
                        'awayTeam' => $firstGame->awayTeam ? [
                            'id' => $firstGame->awayTeam->id,
                            'name' => $firstGame->awayTeam->name
                        ] : null,
                    ]);
                }
            }
            
            Log::info('ClipController@create - Recent games count: ' . $recentGames->count());
            
            // For development/debugging only
            if ($recentGames->isEmpty() && $debugMode) {
                Log::info('Debug mode enabled. Creating temporary fake games with video.');
                
                // Get all team games regardless of video
                $teamGames = Game::where(function($query) use ($team) {
                        $query->where('home_team_id', $team->id)
                            ->orWhere('away_team_id', $team->id);
                    })
                    ->with(['homeTeam', 'awayTeam'])
                    ->orderBy('game_date_time', 'desc')
                    ->limit(5)
                    ->get();
                
                // If we found some team games, temporarily add video URLs
                if ($teamGames->isNotEmpty()) {
                    foreach ($teamGames as $tGame) {
                        // Create a clone with video URL for the frontend
                        $gameWithVideo = $tGame->replicate();
                        $gameWithVideo->video_url = 'https://www.youtube.com/watch?v=dQw4w9WgXcQ';
                        
                        // Add the home and away teams
                        $gameWithVideo->setRelation('homeTeam', $tGame->homeTeam);
                        $gameWithVideo->setRelation('awayTeam', $tGame->awayTeam);
                        
                        $recentGames->push($gameWithVideo);
                    }
                    
                    Log::info('Added ' . $teamGames->count() . ' fake video games for development.');
                }
            }
            
            if ($recentGames->isEmpty()) {
                // Check if there are any games for this team (regardless of video URL)
                $allTeamGames = Game::where(function($query) use ($team) {
                        $query->where('home_team_id', $team->id)
                              ->orWhere('away_team_id', $team->id);
                    })
                    ->get();
                    
                Log::info('ClipController@create - All team games count: ' . $allTeamGames->count());
                
                if ($allTeamGames->count() > 0) {
                    Log::info('Team has games, but none with video URLs. Sample team game:', $allTeamGames->first()->toArray());
                }
                
                // Check if there are any games with video URLs
                $gamesWithVideo = Game::whereNotNull('video_url')->get();
                Log::info('ClipController@create - Games with video count: ' . $gamesWithVideo->count());
                
                if ($gamesWithVideo->count() > 0) {
                    Log::info('There are games with video, but none for this team. Sample game with video:', $gamesWithVideo->first()->toArray());
                }
            }
        }
        
        // Get team roster for player selection
        $players = $team->players()->get();

        Log::info('ClipController@create - Data being sent to view: ', [
            'team_id' => $team->id,
            'game' => $game ? $game->id : null,
            'recentGames' => $recentGames->count(),
            'players' => $players->count()
        ]);

        return Inertia::render('Coach/ClipForm', [
            'team' => $team,
            'game' => $game,
            'recentGames' => $recentGames,
            'players' => $players,
            'debugInfo' => [
                'hasTeamGames' => Game::where(function($query) use ($team) {
                    $query->where('home_team_id', $team->id)
                          ->orWhere('away_team_id', $team->id);
                })->count() > 0,
                'hasGamesWithVideo' => Game::whereNotNull('video_url')->count() > 0,
                'totalGames' => Game::count(),
                'debugMode' => $debugMode
            ]
        ]);
    }

    /**
     * Store a newly created clip in storage.
     */
    public function store(Request $request)
    {
        Log::info('ClipController@store - Starting clip creation');
        try {
            $validated = $request->validate([
                'game_id' => 'required|exists:games,id',
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'required|numeric|min:0',
                'end_time' => 'required|numeric|gt:start_time',
                'players' => 'required|array|min:1',
                'players.*.id' => 'required|exists:players,id',
                'players.*.note' => 'nullable|string',
            ]);

            Log::info('ClipController@store - Validation passed', ['game_id' => $validated['game_id']]);

            $user = Auth::user();
            $team = $user->managedTeam;
            
            if (!$team) {
                Log::error('ClipController@store - User has no managed team', ['user_id' => $user->id]);
                if ($request->wantsJson()) {
                    return response()->json([
                        'message' => 'You are not currently managing any team.'
                    ], 403);
                }
                return redirect()->route('coach.clips')
                    ->with('error', 'You are not currently managing any team.');
            }

            // Verify the game belongs to the coach's team
            $game = Game::where('id', $validated['game_id'])
                ->where(function($query) use ($team) {
                    $query->where('home_team_id', $team->id)
                        ->orWhere('away_team_id', $team->id);
                })
                ->first();
                
            if (!$game) {
                Log::error('ClipController@store - Game not found or not associated with team', [
                    'game_id' => $validated['game_id'],
                    'team_id' => $team->id
                ]);
                if ($request->wantsJson()) {
                    return response()->json([
                        'message' => 'Game not found or you do not have permission to create clips for it.'
                    ], 404);
                }
                return redirect()->route('coach.clips')
                    ->with('error', 'Game not found or you do not have permission to create clips for it.');
            }
            
            // Verify game has video URL
            if (!$game->video_url) {
                Log::error('ClipController@store - Game has no video URL', ['game_id' => $game->id]);
                if ($request->wantsJson()) {
                    return response()->json([
                        'message' => 'Game does not have a video URL. Please add a video URL first.'
                    ], 422);
                }
                return redirect()->route('coach.games.edit', $game)
                    ->with('error', 'Game does not have a video URL. Please add a video URL first.');
            }

            Log::info('ClipController@store - Creating clip record', [
                'game_id' => $validated['game_id'],
                'coach_id' => $user->id,
                'title' => $validated['title']
            ]);

            // Create the clip
            $clip = new Clip();
            $clip->game_id = $validated['game_id'];
            $clip->coach_user_id = $user->id;
            $clip->title = $validated['title'];
            $clip->description = $validated['description'];
            $clip->video_url = $game->video_url;
            $clip->start_time = $validated['start_time'];
            $clip->end_time = $validated['end_time'];
            
            try {
                $clip->save();
                Log::info('ClipController@store - Clip saved successfully', ['clip_id' => $clip->id]);
            } catch (\Exception $e) {
                Log::error('ClipController@store - Error saving clip', [
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'clip_data' => $clip->toArray()
                ]);
                
                if ($request->wantsJson()) {
                    return response()->json([
                        'message' => 'Database error: ' . $e->getMessage()
                    ], 500);
                }
                
                return redirect()->route('coach.clips')
                    ->with('error', 'Error creating clip: ' . $e->getMessage());
            }

            Log::info('ClipController@store - Associating players with clip', [
                'clip_id' => $clip->id, 
                'player_count' => count($validated['players'])
            ]);

            $playerErrors = [];
            
            // Associate players with the clip and broadcast the ClipShared event
            foreach ($validated['players'] as $playerData) {
                $playerId = $playerData['id'];
                
                try {
                    ClipPlayer::create([
                        'clip_id' => $clip->id,
                        'player_id' => $playerId,
                        'coach_note' => $playerData['note'] ?? null,
                        'sent_at' => now(),
                    ]);
                    
                    // Get the player model for broadcasting
                    $player = Player::with('user')->find($playerId);
                    
                    // Only broadcast if player has a user account
                    if ($player && $player->user) {
                        event(new ClipShared($clip, $user, $player->user));
                    }
                } catch (\Exception $e) {
                    Log::error('ClipController@store - Error associating player with clip', [
                        'clip_id' => $clip->id,
                        'player_id' => $playerId,
                        'error' => $e->getMessage()
                    ]);
                    $playerErrors[] = "Player #$playerId: " . $e->getMessage();
                    // Continue with other players even if one fails
                }
            }
            
            if (!empty($playerErrors) && count($playerErrors) === count($validated['players'])) {
                // All player associations failed, this is a critical error
                // Delete the clip and return an error
                try {
                    $clip->delete();
                } catch (\Exception $e) {
                    Log::error('ClipController@store - Error deleting clip after player association failure', [
                        'clip_id' => $clip->id,
                        'error' => $e->getMessage()
                    ]);
                }
                
                $errorMessage = 'Failed to associate any players with the clip. Please try again.';
                
                if ($request->wantsJson()) {
                    return response()->json([
                        'message' => $errorMessage,
                        'errors' => $playerErrors
                    ], 500);
                }
                
                return redirect()->route('coach.clips')
                    ->with('error', $errorMessage);
            }

            Log::info('ClipController@store - Clip creation completed successfully', ['clip_id' => $clip->id]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Clip created and shared with players successfully.',
                    'redirect' => route('coach.clips'),
                    'clip_id' => $clip->id
                ], 200);
            }
            
            return redirect()->route('coach.clips')
                ->with('success', 'Clip created and shared with players successfully.');
                
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('ClipController@store - Validation exception', [
                'errors' => $e->errors(),
            ]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                ], 422);
            }
            
            // Re-throw the validation exception for the web request
            throw $e;
            
        } catch (\Exception $e) {
            Log::error('ClipController@store - Unexpected exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'An unexpected error occurred: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('coach.clips')
                ->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Display the specified clip.
     */
    public function show(Clip $clip)
    {
        $user = Auth::user();
        
        // Check if user is the coach who created the clip
        if ($clip->coach_user_id !== $user->id) {
            Log::warning('ClipController@show - Permission denied', [
                'clip_id' => $clip->id,
                'clip_coach_id' => $clip->coach_user_id,
                'current_user_id' => $user->id
            ]);
            return redirect()->route('coach.clips')
                ->with('error', 'You do not have permission to view this clip.');
        }

        // Load relationships
        $clip->load(['game.homeTeam', 'game.awayTeam', 'players']);
        
        // Ensure video_url is available - use game's video_url if clip doesn't have one
        if (!$clip->video_url && $clip->game && $clip->game->video_url) {
            $clip->video_url = $clip->game->video_url;
            Log::info('ClipController@show - Using game video URL for clip display');
        }

        return Inertia::render('Coach/ClipDetail', [
            'clip' => $clip,
        ]);
    }

    /**
     * Show the form for editing the specified clip.
     */
    public function edit(Clip $clip)
    {
        $user = Auth::user();
        $team = $user->managedTeam;
        
        // Check if user is the coach who created the clip
        if ($clip->coach_user_id !== $user->id) {
            return redirect()->route('coach.clips')
                ->with('error', 'You do not have permission to edit this clip.');
        }

        $clip->load(['game.homeTeam', 'game.awayTeam', 'players']);
        
        // Get team roster for player selection
        $players = $team->players()->get();
        
        // Format existing player data
        $selectedPlayers = $clip->players->map(function($player) {
            $pivotData = $player->pivot;
            return [
                'id' => $player->id,
                'note' => $pivotData->coach_note,
            ];
        });

        return Inertia::render('Coach/ClipForm', [
            'clip' => $clip,
            'team' => $team,
            'game' => $clip->game,
            'players' => $players,
            'selectedPlayers' => $selectedPlayers,
            'isEditing' => true,
        ]);
    }

    /**
     * Update the specified clip in storage.
     */
    public function update(Request $request, Clip $clip)
    {
        try {
            $validated = $request->validate([
                'title' => 'required|string|max:255',
                'description' => 'nullable|string',
                'start_time' => 'required|numeric|min:0',
                'end_time' => 'required|numeric|gt:start_time',
                'players' => 'required|array|min:1',
                'players.*.id' => 'required|exists:players,id',
                'players.*.note' => 'nullable|string',
            ]);

            $user = Auth::user();
            
            // Check if user is the coach who created the clip
            if ($clip->coach_user_id !== $user->id) {
                if ($request->wantsJson()) {
                    return response()->json([
                        'message' => 'You do not have permission to update this clip.'
                    ], 403);
                }
                return redirect()->route('coach.clips')
                    ->with('error', 'You do not have permission to update this clip.');
            }

            // Update the clip
            try {
                $clip->title = $validated['title'];
                $clip->description = $validated['description'];
                $clip->start_time = $validated['start_time'];
                $clip->end_time = $validated['end_time'];
                $clip->save();
                
                Log::info('ClipController@update - Clip updated successfully', ['clip_id' => $clip->id]);
            } catch (\Exception $e) {
                Log::error('ClipController@update - Error updating clip', [
                    'clip_id' => $clip->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                if ($request->wantsJson()) {
                    return response()->json([
                        'message' => 'Database error updating clip: ' . $e->getMessage()
                    ], 500);
                }
                
                return redirect()->route('coach.clips')
                    ->with('error', 'Error updating clip: ' . $e->getMessage());
            }

            // Get the current players associated with the clip
            $existingPlayerIds = $clip->players()->pluck('players.id')->toArray();
            
            try {
                // Update player associations
                // First, remove all existing associations
                ClipPlayer::where('clip_id', $clip->id)->delete();
                
                // Then recreate the associations
                $playerErrors = [];
                foreach ($validated['players'] as $playerData) {
                    $playerId = $playerData['id'];
                    
                    try {
                        ClipPlayer::create([
                            'clip_id' => $clip->id,
                            'player_id' => $playerId,
                            'coach_note' => $playerData['note'] ?? null,
                            'sent_at' => now(),
                        ]);
                        
                        // Only broadcast to newly added players
                        if (!in_array($playerId, $existingPlayerIds)) {
                            // Get the player model for broadcasting
                            $player = Player::with('user')->find($playerId);
                            
                            // Only broadcast if player has a user account
                            if ($player && $player->user) {
                                event(new ClipShared($clip, $user, $player->user));
                            }
                        }
                    } catch (\Exception $e) {
                        Log::error('ClipController@update - Error associating player with clip', [
                            'clip_id' => $clip->id,
                            'player_id' => $playerId,
                            'error' => $e->getMessage()
                        ]);
                        $playerErrors[] = "Player #$playerId: " . $e->getMessage();
                    }
                }
                
                if (!empty($playerErrors) && count($playerErrors) === count($validated['players'])) {
                    // All player associations failed, this is a critical error
                    $errorMessage = 'Failed to associate any players with the clip. Please try again.';
                    
                    if ($request->wantsJson()) {
                        return response()->json([
                            'message' => $errorMessage,
                            'errors' => $playerErrors
                        ], 500);
                    }
                    
                    return redirect()->route('coach.clips')
                        ->with('error', $errorMessage);
                }
            } catch (\Exception $e) {
                Log::error('ClipController@update - Error updating player associations', [
                    'clip_id' => $clip->id,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                if ($request->wantsJson()) {
                    return response()->json([
                        'message' => 'Error updating player associations: ' . $e->getMessage()
                    ], 500);
                }
                
                return redirect()->route('coach.clips')
                    ->with('error', 'Error updating player associations: ' . $e->getMessage());
            }

            Log::info('ClipController@update - Clip and player associations updated successfully', [
                'clip_id' => $clip->id
            ]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'Clip updated successfully.',
                    'redirect' => route('coach.clips.show', $clip),
                    'clip_id' => $clip->id
                ], 200);
            }

            return redirect()->route('coach.clips.show', $clip)
                ->with('success', 'Clip updated successfully.');
        } catch (\Illuminate\Validation\ValidationException $e) {
            Log::error('ClipController@update - Validation exception', [
                'errors' => $e->errors(),
            ]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'The given data was invalid.',
                    'errors' => $e->errors(),
                ], 422);
            }
            
            // Re-throw the validation exception for the web request
            throw $e;
            
        } catch (\Exception $e) {
            Log::error('ClipController@update - Unexpected exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            if ($request->wantsJson()) {
                return response()->json([
                    'message' => 'An unexpected error occurred: ' . $e->getMessage()
                ], 500);
            }
            
            return redirect()->route('coach.clips')
                ->with('error', 'An unexpected error occurred: ' . $e->getMessage());
        }
    }

    /**
     * Remove the specified clip from storage.
     */
    public function destroy(Clip $clip)
    {
        $user = Auth::user();
        
        // Check if user is the coach who created the clip
        if ($clip->coach_user_id !== $user->id) {
            return redirect()->route('coach.clips')
                ->with('error', 'You do not have permission to delete this clip.');
        }

        // Delete the clip (player associations will be deleted via cascade)
        $clip->delete();

        return redirect()->route('coach.clips')
            ->with('success', 'Clip deleted successfully.');
    }
} 