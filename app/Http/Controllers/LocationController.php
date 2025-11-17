<?php

namespace App\Http\Controllers;

use App\Http\Requests\LocationStoreRequest;
use App\Http\Requests\LocationUpdateRequest;
use App\Models\Location;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class LocationController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['1','2'])],
            'sort_by' => ['nullable', Rule::in(['location_iso_code', 'location_name', 'parent_iso_code', 'status', 'created_at'])],
            'sort_dir' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Location::query();

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('location_iso_code', 'like', "%{$search}%")
                  ->orWhere('location_name', 'like', "%{$search}%")
                  ->orWhere('parent_iso_code', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', (int) $status);
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = (int) $request->input('per_page', 10);
        $locations = $query->paginate($perPage)->withQueryString();

        return Inertia::render('locations/Index', [
            'locations' => $locations,
            'filters' => [
                'search' => $request->input('search'),
                'status' => $request->input('status'),
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
                'per_page' => $perPage,
            ],
            'statuses' => [
                ['value' => 1, 'label' => 'Active'],
                ['value' => 2, 'label' => 'Inactive'],
            ],
        ]);
    }

    public function create()
    {
        return Inertia::render('locations/Create');
    }

    public function store(LocationStoreRequest $request)
    {
        $data = $request->validated();
        $data['status'] = $data['status'] ?? 1;
        Location::create($data);

        return redirect()->route('locations.index')->with('success', 'Location created successfully.');
    }

    public function edit(Location $location)
    {
        return Inertia::render('locations/Edit', [
            'location' => $location,
        ]);
    }

    public function update(LocationUpdateRequest $request, Location $location)
    {
        $data = $request->validated();
        $location->update($data);

        return redirect()->route('locations.index')->with('success', 'Location updated successfully.');
    }

    public function destroy(Location $location)
    {
        $location->delete();
        return redirect()->route('locations.index')->with('success', 'Location deleted successfully.');
    }
}
