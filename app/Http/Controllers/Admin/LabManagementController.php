<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Lab;
use Illuminate\Http\Request;

class LabManagementController extends Controller
{
    /**
     * Display list of labs
     */
    public function index()
    {
        $labs = Lab::orderBy('name')->paginate(15);
        return view('admin.labs.index', compact('labs'));
    }

    /**
     * Show create form
     */
    public function create()
    {
        return view('admin.labs.create');
    }

    /**
     * Store new lab
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:labs'],
            'code' => ['required', 'string', 'max:10', 'unique:labs'],
            'description' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        Lab::create($validated);

        return redirect()->route('admin.labs.index')
            ->with('success', '✅ Laboratorium berhasil ditambahkan!');
    }

    /**
     * Show lab detail
     */
    public function show(Lab $lab)
    {
        return view('admin.labs.show', compact('lab'));
    }

    /**
     * Show edit form
     */
    public function edit(Lab $lab)
    {
        return view('admin.labs.edit', compact('lab'));
    }

    /**
     * Update lab
     */
    public function update(Request $request, Lab $lab)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255', 'unique:labs,name,' . $lab->id],
            'code' => ['required', 'string', 'max:10', 'unique:labs,code,' . $lab->id],
            'description' => ['nullable', 'string', 'max:1000'],
            'location' => ['nullable', 'string', 'max:255'],
            'capacity' => ['required', 'integer', 'min:1'],
            'status' => ['required', 'in:active,inactive'],
        ]);

        $lab->update($validated);

        return redirect()->route('admin.labs.index')
            ->with('success', '✅ Laboratorium berhasil diupdate!');
    }

    /**
     * Delete lab
     */
    public function destroy(Lab $lab)
    {
        // Check if lab has bookings
        if ($lab->bookings()->count() > 0) {
            return back()->with('error', '❌ Laboratorium tidak dapat dihapus karena masih ada booking!');
        }

        $lab->delete();

        return redirect()->route('admin.labs.index')
            ->with('success', '✅ Laboratorium berhasil dihapus!');
    }
}
