<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\User;
use App\Models\Role;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;

class AdminTeamController extends Controller
{
    /**
     * Display a listing of the teams with their coaches.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $teams = Team::with(['league', 'season', 'association'])
            ->withCount('players')
            ->orderBy('name')
            ->paginate(10);

        // Get the team coaches (users with managed_team_id)
        $teamCoaches = User::whereNotNull('managed_team_id')
            ->whereIn('managed_team_id', $teams->pluck('id'))
            ->select('id', 'name', 'email', 'managed_team_id')
            ->get()
            ->groupBy('managed_team_id');

        // Attach coach data to each team
        $teams->getCollection()->transform(function ($team) use ($teamCoaches) {
            $team->coaches = $teamCoaches->get($team->id, collect());
            return $team;
        });

        return Inertia::render('Admin/Teams', [
            'teams' => $teams,
        ]);
    }

    /**
     * Show the form for assigning a coach to a team.
     *
     * @param  \App\Models\Team  $team
     * @return \Inertia\Response
     */
    public function assignCoachForm(Team $team)
    {
        $team->load(['league', 'season', 'association']);
        
        // Get current coaches
        $currentCoaches = User::where('managed_team_id', $team->id)
            ->select('id', 'name', 'email')
            ->get();
        
        // Get coach role ID
        $coachRoleId = Role::where('name', 'Coach')->first()->id;
        
        // Get available coaches (users with coach role but not assigned to this team)
        $availableCoaches = User::whereHas('roles', function($query) use ($coachRoleId) {
                $query->where('roles.id', $coachRoleId);
            })
            ->where(function($query) use ($team) {
                $query->whereNull('managed_team_id')
                    ->orWhere('managed_team_id', '!=', $team->id);
            })
            ->select('id', 'name', 'email')
            ->orderBy('name')
            ->get();

        return Inertia::render('Admin/AssignCoach', [
            'team' => $team,
            'currentCoaches' => $currentCoaches,
            'availableCoaches' => $availableCoaches,
        ]);
    }

    /**
     * Assign a coach to a team.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\RedirectResponse
     */
    public function assignCoach(Request $request, Team $team)
    {
        $validated = $request->validate([
            'coach_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['coach_id']);
        
        // Check if the user has the coach role
        $coachRole = Role::where('name', 'Coach')->first();
        
        if (!$user->roles->contains($coachRole->id)) {
            // Add coach role if user doesn't have it
            $user->roles()->attach($coachRole->id);
        }
        
        // Update the user's managed_team_id
        $user->managed_team_id = $team->id;
        $user->save();

        return redirect()->route('admin.teams')
            ->with('success', 'Coach assigned to team successfully.');
    }

    /**
     * Remove a coach from a team.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function removeCoach(Request $request)
    {
        $validated = $request->validate([
            'coach_id' => 'required|exists:users,id',
        ]);

        $user = User::findOrFail($validated['coach_id']);
        
        // Remove managed_team_id
        $user->managed_team_id = null;
        $user->save();

        return redirect()->route('admin.teams')
            ->with('success', 'Coach removed from team successfully.');
    }

    /**
     * Create a new coach user and assign to a team.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Team  $team
     * @return \Illuminate\Http\RedirectResponse
     */
    public function createCoach(Request $request, Team $team)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
        ]);

        DB::transaction(function () use ($validated, $team) {
            // Create user
            $user = User::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'password' => bcrypt($validated['password']),
                'managed_team_id' => $team->id,
            ]);
            
            // Assign coach role
            $coachRole = Role::where('name', 'Coach')->first();
            $user->roles()->attach($coachRole->id);
        });

        return redirect()->route('admin.teams')
            ->with('success', 'Coach created and assigned to team successfully.');
    }
} 