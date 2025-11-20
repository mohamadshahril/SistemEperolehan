<?php

namespace App\Http\Controllers;

use App\Models\ItemUnit;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ItemUnitController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['1','2'])],
            'sort_by' => ['nullable', Rule::in(['code','name','status','created_at'])],
            'sort_dir' => ['nullable', Rule::in(['asc','desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = ItemUnit::query();

        if ($search = $request->string('search')->toString()) {
            $query->where(function($q) use ($search) {
                $q->where('code', 'like', "%{$search}%")
                  ->orWhere('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', (int)$status);
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = (int)$request->input('per_page', 10);
        $units = $query->paginate($perPage)->withQueryString();

        return Inertia::render('item-units/Index', [
            'units' => $units,
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
        return Inertia::render('item-units/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'code' => ['required','string','max:50','unique:item_units,code'],
            'name' => ['required','string','max:100'],
            'description' => ['nullable','string'],
            'status' => ['nullable', Rule::in([1,2])],
        ]);
        $data['status'] = $data['status'] ?? 1;
        ItemUnit::create($data);

        return redirect()->route('item-units.index')->with('success', 'Item unit created successfully.');
    }

    public function edit(ItemUnit $itemUnit)
    {
        return Inertia::render('item-units/Edit', [
            'unit' => $itemUnit,
        ]);
    }

    public function update(Request $request, ItemUnit $itemUnit)
    {
        $data = $request->validate([
            'code' => ['required','string','max:50', Rule::unique('item_units','code')->ignore($itemUnit->id)],
            'name' => ['required','string','max:100'],
            'description' => ['nullable','string'],
            'status' => ['nullable', Rule::in([1,2])],
        ]);
        $itemUnit->update($data);

        return redirect()->route('item-units.index')->with('success', 'Item unit updated successfully.');
    }

    public function destroy(ItemUnit $itemUnit)
    {
        $itemUnit->delete();
        return redirect()->route('item-units.index')->with('success', 'Item unit deleted successfully.');
    }
}
