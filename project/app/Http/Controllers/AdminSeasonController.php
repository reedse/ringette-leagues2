<?php

namespace App\Http\Controllers;

use App\Models\Season;
use App\Models\League;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminSeasonController extends Controller
{
    /**
     * Display a listing of the seasons.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $seasons = Season::with(['league.association'])
            ->withCount(['teams', 'games'])
            ->orderBy('start_date', 'desc')
            ->paginate(15);

        return Inertia::render('Admin/Seasons', [
            'seasons' => $seasons,
        ]);
    }

    /**
     * Show the form for creating a new season.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $leagues = League::with(['association'])->orderBy('name')->get();

        return Inertia::render('Admin/SeasonForm', [
            'leagues' => $leagues,
            'isEditing' => false,
        ]);
    }

    /**
     * Store a newly created season in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'league_id' => 'required|exists:leagues,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        Season::create($validated);

        return redirect()->route('admin.seasons')
            ->with('success', 'Season created successfully.');
    }

    /**
     * Show the form for editing the specified season.
     *
     * @param  \App\Models\Season  $season
     * @return \Inertia\Response
     */
    public function edit(Season $season)
    {
        $season->load(['league.association']);
        $leagues = League::with(['association'])->orderBy('name')->get();

        return Inertia::render('Admin/SeasonForm', [
            'season' => $season,
            'leagues' => $leagues,
            'isEditing' => true,
        ]);
    }

    /**
     * Update the specified season in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Season  $season
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Season $season)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'league_id' => 'required|exists:leagues,id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
        ]);

        $season->update($validated);

        return redirect()->route('admin.seasons')
            ->with('success', 'Season updated successfully.');
    }

    /**
     * Remove the specified season from storage.
     *
     * @param  \App\Models\Season  $season
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Season $season)
    {
        // Check if season has associated teams or games
        if ($season->teams()->count() > 0 || $season->games()->count() > 0) {
            return redirect()->route('admin.seasons')
                ->with('error', 'Cannot delete season that has associated teams or games.');
        }

        $season->delete();

        return redirect()->route('admin.seasons')
            ->with('success', 'Season deleted successfully.');
    }
} 