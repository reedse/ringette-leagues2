<?php

namespace App\Http\Controllers;

use App\Models\League;
use App\Models\Association;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminLeagueController extends Controller
{
    /**
     * Display a listing of the leagues.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $leagues = League::with(['association'])
            ->withCount(['teams', 'seasons'])
            ->orderBy('name')
            ->paginate(15);

        return Inertia::render('Admin/Leagues', [
            'leagues' => $leagues,
        ]);
    }

    /**
     * Show the form for creating a new league.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        $associations = Association::orderBy('name')->get();

        return Inertia::render('Admin/LeagueForm', [
            'associations' => $associations,
            'isEditing' => false,
        ]);
    }

    /**
     * Store a newly created league in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'association_id' => 'required|exists:associations,id',
        ]);

        League::create($validated);

        return redirect()->route('admin.leagues')
            ->with('success', 'League created successfully.');
    }

    /**
     * Show the form for editing the specified league.
     *
     * @param  \App\Models\League  $league
     * @return \Inertia\Response
     */
    public function edit(League $league)
    {
        $league->load(['association']);
        $associations = Association::orderBy('name')->get();

        return Inertia::render('Admin/LeagueForm', [
            'league' => $league,
            'associations' => $associations,
            'isEditing' => true,
        ]);
    }

    /**
     * Update the specified league in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\League  $league
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, League $league)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'association_id' => 'required|exists:associations,id',
        ]);

        $league->update($validated);

        return redirect()->route('admin.leagues')
            ->with('success', 'League updated successfully.');
    }

    /**
     * Remove the specified league from storage.
     *
     * @param  \App\Models\League  $league
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(League $league)
    {
        // Check if league has associated teams or seasons
        if ($league->teams()->count() > 0 || $league->seasons()->count() > 0) {
            return redirect()->route('admin.leagues')
                ->with('error', 'Cannot delete league that has associated teams or seasons.');
        }

        $league->delete();

        return redirect()->route('admin.leagues')
            ->with('success', 'League deleted successfully.');
    }
} 