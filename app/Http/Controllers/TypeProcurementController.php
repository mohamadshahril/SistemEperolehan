<?php

namespace App\Http\Controllers;

use App\Models\TypeProcurement;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TypeProcurementController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['1','2'])],
            'sort_by' => ['nullable', Rule::in(['procurement_code','procurement_description','status','created_at'])],
            'sort_dir' => ['nullable', Rule::in(['asc','desc'])],
            'page' => ['nullable','integer','min:1'],
            'per_page' => ['nullable','integer','min:1','max:100'],
        ]);

        $query = TypeProcurement::query();

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('procurement_code', 'like', "%{$search}%")
                  ->orWhere('procurement_description', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', (int) $status);
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = (int) $request->input('per_page', 10);
        $items = $query->paginate($perPage)->withQueryString();

        return Inertia::render('type-procurements/Index', [
            'typeProcurements' => $items,
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
        return Inertia::render('type-procurements/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            // Alphanumeric code like TP01
            'procurement_code' => ['required','string','max:20','regex:/^[A-Za-z0-9_-]+$/','unique:type_procurements,procurement_code'],
            'procurement_description' => ['required','string','max:100'],
            'status' => ['nullable','integer', Rule::in([1,2])],
        ]);

        $data['status'] = $data['status'] ?? 1;

        TypeProcurement::create($data);

        return redirect()->route('type-procurements.index')->with('success', 'Type Procurement created successfully.');
    }

    public function edit(TypeProcurement $type_procurement)
    {
        return Inertia::render('type-procurements/Edit', [
            'typeProcurement' => $type_procurement,
        ]);
    }

    public function update(Request $request, TypeProcurement $type_procurement)
    {
        $data = $request->validate([
            'procurement_code' => ['required','string','max:20','regex:/^[A-Za-z0-9_-]+$/', Rule::unique('type_procurements','procurement_code')->ignore($type_procurement->id)],
            'procurement_description' => ['required','string','max:100'],
            'status' => ['required','integer', Rule::in([1,2])],
        ]);

        $type_procurement->update($data);

        return redirect()->route('type-procurements.index')->with('success', 'Type Procurement updated successfully.');
    }

    public function destroy(TypeProcurement $type_procurement)
    {
        $type_procurement->delete();
        return redirect()->route('type-procurements.index')->with('success', 'Type Procurement deleted successfully.');
    }
}
