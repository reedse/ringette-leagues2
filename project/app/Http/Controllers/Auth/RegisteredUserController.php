<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\Player;
use App\Models\Team;
use App\Models\Association;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): Response
    {
        // Fetch Associations with their teams (including season/league for context)
        $associations = Association::with(['teams.season.league'])
            ->whereHas('teams') // Only get associations with teams
            ->orderBy('name')
            ->get()
            ->map(function ($association) {
                return [
                    'id' => $association->id,
                    'name' => $association->name,
                    // Group teams by season/league maybe? Or just list them.
                    // Let's just list them with descriptive names for now.
                    'teams' => $association->teams->map(function ($team) {
                        return [
                            'id' => $team->id,
                            // Construct a more descriptive name
                            'name' => $team->name . ' (' . ($team->season->league->name ?? 'N/A') . ' - ' . ($team->season->name ?? 'N/A') . ')',
                        ];
                    })->sortBy('name')->values(), // Sort teams alphabetically within association
                ];
            });

        return Inertia::render('Auth/Register', [
            'associations' => $associations // Pass structured data
        ]);
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|lowercase|email|max:255|unique:'.User::class,
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'role' => ['required', 'string', Rule::in(['player', 'coach'])],
            'team_id' => ['required_if:role,player', 'nullable', 'integer', Rule::exists(Team::class, 'id')],
            'jersey_number' => ['required_if:role,player', 'nullable', 'string', 'max:10'],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // Assign the selected role
        $selectedRole = Role::where('name', $request->role)->firstOrFail();
        $user->roles()->attach($selectedRole);

        // --- Player Linking/Creation Logic ---
        if ($request->role === 'player') {
            $teamId = $request->input('team_id');
            $jerseyNumber = $request->input('jersey_number');

            // Try to find an unlinked player with the same jersey number in the roster for this team
            $player = Player::whereHas('rosterEntries', function ($query) use ($teamId) {
                        $query->where('team_id', $teamId);
                    })
                    ->where('jersey_number', $jerseyNumber)
                    ->whereNull('user_id')
                    ->first();

            if ($player) {
                // Link existing player record
                $player->user_id = $user->id;
                $player->save();
            } else {
                // Create a new player record and add to the team roster
                $nameparts = explode(' ', $user->name, 2);
                $firstName = $nameparts[0];
                $lastName = isset($nameparts[1]) ? $nameparts[1] : '';
                
                $player = Player::create([
                    'user_id' => $user->id,
                    'first_name' => $firstName,
                    'last_name' => $lastName,
                    'jersey_number' => $jerseyNumber,
                ]);
                
                // Get the current season for this team
                $team = Team::with('season')->findOrFail($teamId);
                
                // Add player to team roster
                $player->teams()->attach($teamId, [
                    'season_id' => $team->season->id,
                ]);
            }
        }
        // --- End Player Linking ---

        event(new Registered($user));

        Auth::login($user);

        return redirect(route('dashboard', absolute: false));
    }
}
