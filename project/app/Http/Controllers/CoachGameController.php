<?php

namespace App\Http\Controllers;

use App\Models\Game;
use App\Models\Team;
use App\Models\League;
use App\Models\Season;
use App\Models\Player;
use App\Models\PenaltyCode;
use App\Models\PlayerGameStat;
use App\Models\GamePenalty;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class CoachGameController extends Controller
{
    /**
     * Display a listing of games for the coach's team.
     */
    public function index()
    {
        $user = Auth::user();
        $team = $user->managedTeam;
        
        if (!$team) {
            return Inertia::render('Coach/Games', [
                'error' => 'You are not currently managing any team.',
                'games' => [],
            ]);
        }

        $games = Game::where('home_team_id', $team->id)
            ->orWhere('away_team_id', $team->id)
            ->with(['homeTeam', 'awayTeam', 'league', 'season'])
            ->orderBy('game_date_time', 'desc')
            ->get();

        return Inertia::render('Coach/Games', [
            'team' => $team,
            'games' => $games,
        ]);
    }

    /**
     * Show the form for creating a new game.
     */
    public function create()
    {
        $user = Auth::user();
        $team = $user->managedTeam;
        
        if (!$team) {
            return redirect()->route('coach.games')
                ->with('error', 'You are not currently managing any team.');
        }

        $leagues = League::all();
        $seasons = Season::all();
        $teams = Team::where('id', '!=', $team->id)->get();

        return Inertia::render('Coach/GameForm', [
            'team' => $team,
            'leagues' => $leagues,
            'seasons' => $seasons,
            'teams' => $teams,
            'isHome' => true,
        ]);
    }

    /**
     * Store a newly created game in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'league_id' => 'required|exists:leagues,id',
            'season_id' => 'required|exists:seasons,id',
            'opponent_team_id' => 'required|exists:teams,id',
            'is_home' => 'required|boolean',
            'game_date_time' => 'required|date',
            'location' => 'nullable|string|max:255',
            'video_url' => 'nullable|url|max:255',
        ]);

        $user = Auth::user();
        $team = $user->managedTeam;
        
        if (!$team) {
            return redirect()->route('coach.games')
                ->with('error', 'You are not currently managing any team.');
        }

        $game = new Game();
        $game->league_id = $validated['league_id'];
        $game->season_id = $validated['season_id'];
        
        if ($validated['is_home']) {
            $game->home_team_id = $team->id;
            $game->away_team_id = $validated['opponent_team_id'];
        } else {
            $game->home_team_id = $validated['opponent_team_id'];
            $game->away_team_id = $team->id;
        }
        
        $game->game_date_time = $validated['game_date_time'];
        $game->location = $validated['location'] ?? null;
        $game->video_url = $validated['video_url'] ?? null;
        $game->status = 'Scheduled';
        $game->save();

        return redirect()->route('coach.games.edit', $game)
            ->with('success', 'Game created successfully. Now you can add player statistics.');
    }

    /**
     * Display the specified game.
     */
    public function show(Game $game)
    {
        $user = Auth::user();
        $team = $user->managedTeam;
        
        if (!$team || ($game->home_team_id !== $team->id && $game->away_team_id !== $team->id)) {
            return redirect()->route('coach.games')
                ->with('error', 'You do not have permission to view this game.');
        }

        $game->load(['homeTeam', 'awayTeam', 'league', 'season', 'playerGameStats.player', 'gamePenalties.player', 'gamePenalties.penaltyCode']);

        return Inertia::render('Coach/GameDetail', [
            'game' => $game,
            'team' => $team,
            'isHome' => $game->home_team_id === $team->id,
        ]);
    }

    /**
     * Show the form for editing the specified game.
     */
    public function edit(Game $game)
    {
        $user = Auth::user();
        $team = $user->managedTeam;
        
        if (!$team || ($game->home_team_id !== $team->id && $game->away_team_id !== $team->id)) {
            return redirect()->route('coach.games')
                ->with('error', 'You do not have permission to edit this game.');
        }

        $game->load(['homeTeam', 'awayTeam', 'league', 'season']);
        
        $isHome = $game->home_team_id === $team->id;
        $teamId = $team->id;
        
        // Get roster for the team
        $roster = $team->players()->get();
        
        // Get existing stats
        $playerStats = PlayerGameStat::where('game_id', $game->id)
            ->where('team_id', $teamId)
            ->with('player')
            ->get();
            
        // Get existing penalties
        $penalties = GamePenalty::where('game_id', $game->id)
            ->where('team_id', $teamId)
            ->with(['player', 'penaltyCode'])
            ->get();
            
        // Get penalty codes
        $penaltyCodes = PenaltyCode::all();

        return Inertia::render('Coach/GameEdit', [
            'game' => $game,
            'team' => $team,
            'isHome' => $isHome,
            'roster' => $roster,
            'playerStats' => $playerStats,
            'penalties' => $penalties,
            'penaltyCodes' => $penaltyCodes,
        ]);
    }

    /**
     * Update the specified game in storage.
     */
    public function update(Request $request, Game $game)
    {
        $user = Auth::user();
        $team = $user->managedTeam;
        
        if (!$team || ($game->home_team_id !== $team->id && $game->away_team_id !== $team->id)) {
            return redirect()->route('coach.games')
                ->with('error', 'You do not have permission to update this game.');
        }

        $validated = $request->validate([
            'location' => 'nullable|string|max:255',
            'video_url' => 'nullable|url|max:255',
            'status' => 'required|in:Scheduled,In Progress,Completed',
            'home_score' => 'nullable|integer|min:0',
            'away_score' => 'nullable|integer|min:0',
        ]);

        $game->update($validated);

        return redirect()->route('coach.games.edit', $game)
            ->with('success', 'Game updated successfully.');
    }

    /**
     * Update player stats for the game.
     */
    public function updatePlayerStats(Request $request, Game $game)
    {
        $user = Auth::user();
        $team = $user->managedTeam;
        
        if (!$team || ($game->home_team_id !== $team->id && $game->away_team_id !== $team->id)) {
            return redirect()->route('coach.games')
                ->with('error', 'You do not have permission to update stats for this game.');
        }

        $validated = $request->validate([
            'stats' => 'required|array',
            'stats.*.player_id' => 'required|exists:players,id',
            'stats.*.goals' => 'required|integer|min:0',
            'stats.*.assists' => 'required|integer|min:0',
            'stats.*.plus_minus' => 'required|integer',
        ]);

        foreach ($validated['stats'] as $stat) {
            PlayerGameStat::updateOrCreate(
                [
                    'game_id' => $game->id,
                    'player_id' => $stat['player_id'],
                    'team_id' => $team->id,
                ],
                [
                    'goals' => $stat['goals'],
                    'assists' => $stat['assists'],
                    'plus_minus' => $stat['plus_minus'],
                ]
            );
        }

        return redirect()->route('coach.games.edit', $game)
            ->with('success', 'Player statistics updated successfully.');
    }

    /**
     * Update penalties for the game.
     */
    public function updatePenalties(Request $request, Game $game)
    {
        $user = Auth::user();
        $team = $user->managedTeam;
        
        if (!$team || ($game->home_team_id !== $team->id && $game->away_team_id !== $team->id)) {
            return redirect()->route('coach.games')
                ->with('error', 'You do not have permission to update penalties for this game.');
        }

        $validated = $request->validate([
            'penalties' => 'required|array',
            'penalties.*.id' => 'nullable|exists:game_penalties,id',
            'penalties.*.player_id' => 'required|exists:players,id',
            'penalties.*.penalty_code_id' => 'required|exists:penalty_codes,id',
            'penalties.*.period' => 'required|integer|min:1|max:4',
            'penalties.*.time' => 'required|string',
            'penalties.*.delete' => 'nullable|boolean',
        ]);

        foreach ($validated['penalties'] as $penaltyData) {
            if (!empty($penaltyData['id']) && !empty($penaltyData['delete'])) {
                // Delete existing penalty
                GamePenalty::where('id', $penaltyData['id'])->delete();
            } else if (!empty($penaltyData['id'])) {
                // Update existing penalty
                GamePenalty::where('id', $penaltyData['id'])->update([
                    'player_id' => $penaltyData['player_id'],
                    'penalty_code_id' => $penaltyData['penalty_code_id'],
                    'period' => $penaltyData['period'],
                    'time' => $penaltyData['time'],
                ]);
            } else if (empty($penaltyData['delete'])) {
                // Create new penalty
                GamePenalty::create([
                    'game_id' => $game->id,
                    'team_id' => $team->id,
                    'player_id' => $penaltyData['player_id'],
                    'penalty_code_id' => $penaltyData['penalty_code_id'],
                    'period' => $penaltyData['period'],
                    'time' => $penaltyData['time'],
                ]);
            }
        }

        return redirect()->route('coach.games.edit', $game)
            ->with('success', 'Game penalties updated successfully.');
    }
} 