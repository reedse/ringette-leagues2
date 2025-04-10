<?php

namespace App\Http\Controllers;

use App\Models\Clip;
use App\Models\ClipPlayer;
use App\Models\Game;
use App\Models\Player;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
        }
        
        if (!$game && $gameId) {
            return redirect()->route('coach.clips')
                ->with('error', 'Game not found or you do not have permission to view it.');
        }
        
        // If no game specified, get recent games for selection
        $recentGames = [];
        if (!$game) {
            $recentGames = Game::where(function($query) use ($team) {
                    $query->where('home_team_id', $team->id)
                          ->orWhere('away_team_id', $team->id);
                })
                ->where('video_url', '!=', null)
                ->with(['homeTeam', 'awayTeam'])
                ->orderBy('game_date_time', 'desc')
                ->limit(10)
                ->get();
        }
        
        // Get team roster for player selection
        $players = $team->players()->get();

        return Inertia::render('Coach/ClipForm', [
            'team' => $team,
            'game' => $game,
            'recentGames' => $recentGames,
            'players' => $players,
        ]);
    }

    /**
     * Store a newly created clip in storage.
     */
    public function store(Request $request)
    {
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

        $user = Auth::user();
        $team = $user->managedTeam;
        
        if (!$team) {
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
            return redirect()->route('coach.clips')
                ->with('error', 'Game not found or you do not have permission to create clips for it.');
        }
        
        // Verify game has video URL
        if (!$game->video_url) {
            return redirect()->route('coach.games.edit', $game)
                ->with('error', 'Game does not have a video URL. Please add a video URL first.');
        }

        // Create the clip
        $clip = new Clip();
        $clip->game_id = $validated['game_id'];
        $clip->coach_user_id = $user->id;
        $clip->title = $validated['title'];
        $clip->description = $validated['description'];
        $clip->video_url = $game->video_url;
        $clip->start_time = $validated['start_time'];
        $clip->end_time = $validated['end_time'];
        $clip->save();

        // Associate players with the clip
        foreach ($validated['players'] as $playerData) {
            ClipPlayer::create([
                'clip_id' => $clip->id,
                'player_id' => $playerData['id'],
                'coach_note' => $playerData['note'] ?? null,
                'sent_at' => now(),
            ]);
        }

        return redirect()->route('coach.clips')
            ->with('success', 'Clip created and shared with players successfully.');
    }

    /**
     * Display the specified clip.
     */
    public function show(Clip $clip)
    {
        $user = Auth::user();
        
        // Check if user is the coach who created the clip
        if ($clip->coach_user_id !== $user->id) {
            return redirect()->route('coach.clips')
                ->with('error', 'You do not have permission to view this clip.');
        }

        $clip->load(['game.homeTeam', 'game.awayTeam', 'players']);

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
            return redirect()->route('coach.clips')
                ->with('error', 'You do not have permission to update this clip.');
        }

        // Update the clip
        $clip->title = $validated['title'];
        $clip->description = $validated['description'];
        $clip->start_time = $validated['start_time'];
        $clip->end_time = $validated['end_time'];
        $clip->save();

        // Update player associations
        // First, remove all existing associations
        ClipPlayer::where('clip_id', $clip->id)->delete();
        
        // Then recreate the associations
        foreach ($validated['players'] as $playerData) {
            ClipPlayer::create([
                'clip_id' => $clip->id,
                'player_id' => $playerData['id'],
                'coach_note' => $playerData['note'] ?? null,
                'sent_at' => now(),
            ]);
        }

        return redirect()->route('coach.clips.show', $clip)
            ->with('success', 'Clip updated successfully.');
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