<?php

namespace App\Http\Controllers;

use App\Models\Vot;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class VotController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['1','2'])],
            'sort_by' => ['nullable', Rule::in(['vot_code','vot_description','status','created_at'])],
            'sort_dir' => ['nullable', Rule::in(['asc','desc'])],
            'page' => ['nullable','integer','min:1'],
            'per_page' => ['nullable','integer','min:1','max:100'],
        ]);

        $query = Vot::query();

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('vot_code', 'like', "%{$search}%")
                  ->orWhere('vot_description', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', (int) $status);
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = (int) $request->input('per_page', 10);
        $vots = $query->paginate($perPage)->withQueryString();

        return Inertia::render('vots/Index', [
            'vots' => $vots,
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
        return Inertia::render('vots/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            // Alphanumeric code like V01
            'vot_code' => ['required','string','max:20','regex:/^[A-Za-z0-9_-]+$/','unique:vots,vot_code'],
            'vot_description' => ['required','string','max:255'],
            'status' => ['nullable','integer', Rule::in([1,2])],
        ]);

        $data['status'] = $data['status'] ?? 1;

        Vot::create($data);

        return redirect()->route('vots.index')->with('success', 'Vot created successfully.');
    }

    public function edit(Vot $vot)
    {
        return Inertia::render('vots/Edit', [
            'vot' => $vot,
        ]);
    }

    public function update(Request $request, Vot $vot)
    {
        $data = $request->validate([
            'vot_code' => ['required','string','max:20','regex:/^[A-Za-z0-9_-]+$/', Rule::unique('vots','vot_code')->ignore($vot->id)],
            'vot_description' => ['required','string','max:255'],
            'status' => ['required','integer', Rule::in([1,2])],
        ]);

        $vot->update($data);

        return redirect()->route('vots.index')->with('success', 'Vot updated successfully.');
    }

    public function destroy(Vot $vot)
    {
        $vot->delete();
        return redirect()->route('vots.index')->with('success', 'Vot deleted successfully.');
    }
}
