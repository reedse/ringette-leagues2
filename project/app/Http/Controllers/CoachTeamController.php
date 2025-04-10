<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\Player;
use App\Models\Season;
use App\Models\RosterEntry;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CoachTeamController extends Controller
{
    /**
     * Display the coach's team information.
     */
    public function index()
    {
        $user = Auth::user();
        $team = $user->managedTeam;
        
        if (!$team) {
            return Inertia::render('Coach/Team', [
                'error' => 'You are not currently managing any team.',
                'team' => null,
                'players' => [],
                'seasons' => [],
            ]);
        }

        $team->load(['association', 'league', 'season']);
        
        // Get current roster
        $currentSeason = $team->season;
        $roster = $team->players()->get();
        
        // Get available seasons
        $seasons = Season::where('league_id', $team->league_id)->get();

        return Inertia::render('Coach/Team', [
            'team' => $team,
            'players' => $roster,
            'seasons' => $seasons,
            'currentSeason' => $currentSeason,
        ]);
    }

    /**
     * Show form for adding a player to the roster.
     */
    public function addPlayerForm()
    {
        $user = Auth::user();
        $team = $user->managedTeam;
        
        if (!$team) {
            return redirect()->route('coach.team')
                ->with('error', 'You are not currently managing any team.');
        }

        // Get current roster player IDs to exclude from search
        $currentPlayerIds = $team->players()->pluck('players.id')->toArray();

        return Inertia::render('Coach/AddPlayer', [
            'team' => $team,
            'currentPlayerIds' => $currentPlayerIds,
        ]);
    }

    /**
     * Search for players to add to the roster.
     */
    public function searchPlayers(Request $request)
    {
        $validated = $request->validate([
            'search' => 'required|string|min:2',
            'exclude' => 'nullable|array',
        ]);

        $search = $validated['search'];
        $excludeIds = $validated['exclude'] ?? [];

        $players = Player::where(function($query) use ($search) {
                $query->where('first_name', 'like', "%{$search}%")
                      ->orWhere('last_name', 'like', "%{$search}%")
                      ->orWhere('jersey_number', 'like', "%{$search}%");
            })
            ->whereNotIn('id', $excludeIds)
            ->limit(10)
            ->get();

        return response()->json([
            'players' => $players
        ]);
    }

    /**
     * Add a player to the team roster.
     */
    public function addPlayer(Request $request)
    {
        $validated = $request->validate([
            'player_id' => 'required|exists:players,id',
        ]);

        $user = Auth::user();
        $team = $user->managedTeam;
        
        if (!$team) {
            return redirect()->route('coach.team')
                ->with('error', 'You are not currently managing any team.');
        }

        // Check if player is already on this team's roster
        $exists = RosterEntry::where('player_id', $validated['player_id'])
            ->where('team_id', $team->id)
            ->where('season_id', $team->season_id)
            ->exists();

        if ($exists) {
            return redirect()->route('coach.team')
                ->with('error', 'Player is already on the roster for this season.');
        }

        // Add player to roster
        RosterEntry::create([
            'player_id' => $validated['player_id'],
            'team_id' => $team->id,
            'season_id' => $team->season_id,
        ]);

        return redirect()->route('coach.team')
            ->with('success', 'Player added to roster successfully.');
    }

    /**
     * Remove a player from the team roster.
     */
    public function removePlayer(Request $request)
    {
        $validated = $request->validate([
            'player_id' => 'required|exists:players,id',
        ]);

        $user = Auth::user();
        $team = $user->managedTeam;
        
        if (!$team) {
            return redirect()->route('coach.team')
                ->with('error', 'You are not currently managing any team.');
        }

        // Remove player from roster
        RosterEntry::where('player_id', $validated['player_id'])
            ->where('team_id', $team->id)
            ->where('season_id', $team->season_id)
            ->delete();

        return redirect()->route('coach.team')
            ->with('success', 'Player removed from roster successfully.');
    }

    /**
     * Create a new player and add to roster.
     */
    public function createPlayer(Request $request)
    {
        $validated = $request->validate([
            'first_name' => 'required|string|max:255',
            'last_name' => 'required|string|max:255',
            'jersey_number' => 'required|string|max:10',
            'position' => 'nullable|string|max:50',
            'date_of_birth' => 'nullable|date',
        ]);

        $user = Auth::user();
        $team = $user->managedTeam;
        
        if (!$team) {
            return redirect()->route('coach.team')
                ->with('error', 'You are not currently managing any team.');
        }

        // Create new player
        $player = Player::create($validated);

        // Add to roster
        RosterEntry::create([
            'player_id' => $player->id,
            'team_id' => $team->id,
            'season_id' => $team->season_id,
        ]);

        return redirect()->route('coach.team')
            ->with('success', 'Player created and added to roster successfully.');
    }
} 