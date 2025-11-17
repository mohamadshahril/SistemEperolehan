<?php

namespace App\Http\Controllers;

use App\Models\FileReference;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class FileReferenceController extends Controller
{
    public function index(Request $request)
    {
        $request->validate([
            'search' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['1','2'])],
            'sort_by' => ['nullable', Rule::in(['file_code','file_description','parent_file_code','status','created_at'])],
            'sort_dir' => ['nullable', Rule::in(['asc','desc'])],
            'page' => ['nullable','integer','min:1'],
            'per_page' => ['nullable','integer','min:1','max:100'],
        ]);

        $query = FileReference::query();

        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('file_code', 'like', "%{$search}%")
                  ->orWhere('file_description', 'like', "%{$search}%")
                  ->orWhere('parent_file_code', 'like', "%{$search}%");
            });
        }

        if ($status = $request->input('status')) {
            $query->where('status', (int) $status);
        }

        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $perPage = (int) $request->input('per_page', 10);
        $fileReferences = $query->paginate($perPage)->withQueryString();

        return Inertia::render('file-references/Index', [
            'fileReferences' => $fileReferences,
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
        return Inertia::render('file-references/Create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'file_code' => ['required','string','max:255','unique:file_references,file_code'],
            'file_description' => ['required','string','max:50'],
            'parent_file_code' => ['required','string','max:255'],
            'status' => ['nullable','integer', Rule::in([1,2])],
        ]);

        $data['status'] = $data['status'] ?? 1;

        FileReference::create($data);

        return redirect()->route('file-references.index')->with('success', 'File reference created successfully.');
    }

    public function edit(FileReference $file_reference)
    {
        return Inertia::render('file-references/Edit', [
            'fileReference' => $file_reference,
        ]);
    }

    public function update(Request $request, FileReference $file_reference)
    {
        $data = $request->validate([
            'file_code' => ['required','string','max:255', Rule::unique('file_references','file_code')->ignore($file_reference->id)],
            'file_description' => ['required','string','max:50'],
            'parent_file_code' => ['required','string','max:255'],
            'status' => ['required','integer', Rule::in([1,2])],
        ]);

        $file_reference->update($data);

        return redirect()->route('file-references.index')->with('success', 'File reference updated successfully.');
    }

    public function destroy(FileReference $file_reference)
    {
        $file_reference->delete();
        return redirect()->route('file-references.index')->with('success', 'File reference deleted successfully.');
    }
}
