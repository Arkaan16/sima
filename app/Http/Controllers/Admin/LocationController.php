<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Location;
use Illuminate\Http\Request;

class LocationController extends Controller
{
    public function index()
    {
        $locations = Location::with('children')
            ->whereNull('parent_id')
            ->latest()
            ->paginate(10);
            
        return view('admin.locations.index', compact('locations'));
    }

    public function create()
    {
        // Hanya ambil lokasi utama (parent) untuk dipilih di dropdown
        $parentLocations = Location::whereNull('parent_id')->get();
        return view('admin.locations.create', compact('parentLocations'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:locations,id'
        ]);

        Location::create($request->all());

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location created successfully.');
    }

    public function edit(Location $location)
    {
        $parentLocations = Location::whereNull('parent_id')
            ->where('id', '!=', $location->id) // Agar tidak memilih diri sendiri sebagai parent
            ->get();
            
        return view('admin.locations.edit', compact('location', 'parentLocations'));
    }

    public function update(Request $request, Location $location)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'parent_id' => 'nullable|exists:locations,id'
        ]);

        $location->update($request->all());

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location)
    {
        // Ini juga akan menghapus sub-lokasi karena 'onDelete(cascade)' di migrasi
        $location->delete();

        return redirect()->route('admin.locations.index')
            ->with('success', 'Location deleted successfully.');
    }
}
