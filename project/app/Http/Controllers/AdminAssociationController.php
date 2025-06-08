<?php

namespace App\Http\Controllers;

use App\Models\Association;
use Illuminate\Http\Request;
use Inertia\Inertia;

class AdminAssociationController extends Controller
{
    /**
     * Display a listing of the associations.
     *
     * @return \Inertia\Response
     */
    public function index()
    {
        $associations = Association::withCount(['leagues', 'teams'])
            ->orderBy('name')
            ->paginate(15);

        return Inertia::render('Admin/Associations', [
            'associations' => $associations,
        ]);
    }

    /**
     * Show the form for creating a new association.
     *
     * @return \Inertia\Response
     */
    public function create()
    {
        return Inertia::render('Admin/AssociationForm', [
            'isEditing' => false,
        ]);
    }

    /**
     * Store a newly created association in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\RedirectResponse
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:associations,name',
        ]);

        Association::create($validated);

        return redirect()->route('admin.associations')
            ->with('success', 'Association created successfully.');
    }

    /**
     * Show the form for editing the specified association.
     *
     * @param  \App\Models\Association  $association
     * @return \Inertia\Response
     */
    public function edit(Association $association)
    {
        return Inertia::render('Admin/AssociationForm', [
            'association' => $association,
            'isEditing' => true,
        ]);
    }

    /**
     * Update the specified association in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Association  $association
     * @return \Illuminate\Http\RedirectResponse
     */
    public function update(Request $request, Association $association)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:associations,name,' . $association->id,
        ]);

        $association->update($validated);

        return redirect()->route('admin.associations')
            ->with('success', 'Association updated successfully.');
    }

    /**
     * Remove the specified association from storage.
     *
     * @param  \App\Models\Association  $association
     * @return \Illuminate\Http\RedirectResponse
     */
    public function destroy(Association $association)
    {
        // Check if association has associated leagues or teams
        if ($association->leagues()->count() > 0 || $association->teams()->count() > 0) {
            return redirect()->route('admin.associations')
                ->with('error', 'Cannot delete association that has associated leagues or teams.');
        }

        $association->delete();

        return redirect()->route('admin.associations')
            ->with('success', 'Association deleted successfully.');
    }
} 