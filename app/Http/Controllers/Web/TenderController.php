<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Tender;
use App\Models\TenderBid;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TenderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['Draft', 'Published', 'Closed', 'Awarded', 'Cancelled'])],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'date_filter_type' => ['nullable', Rule::in(['created_at', 'opening_date', 'closing_date'])],
            'sort_by' => ['nullable', Rule::in(['tender_number', 'title', 'opening_date', 'closing_date', 'status', 'created_at'])],
            'sort_dir' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Tender::query()
            ->withCount('bids')
            ->with([
                'creator' => function ($q) {
                    $q->withTrashed()->select('id', 'name');
                },
                'awardedBid.vendor:id,name',
                'bids' => function ($query) {
                    $query->select('id', 'tender_id', 'vendor_id', 'bid_amount', 'status', 'submitted_at')
                        ->with('vendor:id,name')
                        ->orderBy('bid_amount', 'asc')
                        ->limit(10); // Limit to 10 bids for performance
                }
            ]);

        // Search by tender number, title, or description
        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('tender_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status
        if ($status = $request->input('status')) {
            $query->where('status', $status);
        }

        // Filter by date range
        $dateFilterType = $request->input('date_filter_type', 'created_at');
        if ($fromDate = $request->input('from_date')) {
            $query->whereDate($dateFilterType, '>=', $fromDate);
        }
        if ($toDate = $request->input('to_date')) {
            $query->whereDate($dateFilterType, '<=', $toDate);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = (int) $request->input('per_page', 10);
        $tenders = $query->paginate($perPage)->withQueryString();

        return Inertia::render('tenders/Index', [
            'tenders' => $tenders,
            'filters' => [
                'search' => $request->input('search'),
                'status' => $request->input('status'),
                'from_date' => $request->input('from_date'),
                'to_date' => $request->input('to_date'),
                'date_filter_type' => $request->input('date_filter_type'),
                'sort_by' => $request->input('sort_by'),
                'sort_dir' => $request->input('sort_dir'),
                'per_page' => $request->input('per_page'),
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return Inertia::render('tenders/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255', 'unique:tenders,title'],
            'description' => ['required', 'string'],
            'estimated_budget' => ['nullable', 'numeric', 'min:0'],
            'opening_date' => ['required', 'date', 'after_or_equal:today'],
            'closing_date' => ['required', 'date', 'after:opening_date'],
            'status' => ['required', Rule::in(['Draft', 'Published'])],
            'requirements' => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],
            'documents' => ['nullable', 'array'],
            'documents.*' => ['file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'],
            'document_descriptions' => ['nullable', 'array'],
            'document_descriptions.*' => ['nullable', 'string', 'max:255'],
        ]);

        $validated['tender_number'] = Tender::generateTenderNumber();
        $validated['created_by'] = auth()->id();

        $tender = Tender::create($validated);

        // Handle document uploads
        if ($request->hasFile('documents')) {
            foreach ($request->file('documents') as $index => $file) {
                $fileName = time() . '_' . $file->getClientOriginalName();
                $filePath = $file->storeAs('tender-documents', $fileName, 'public');

                $tender->documents()->create([
                    'file_name' => $file->getClientOriginalName(),
                    'file_path' => $filePath,
                    'file_type' => $file->getClientMimeType(),
                    'file_size' => $file->getSize(),
                    'description' => $request->input("document_descriptions.{$index}"),
                    'uploaded_by' => auth()->id(),
                ]);
            }
        }

        return redirect()
            ->route('tenders.show', $tender)
            ->with('success', 'Tender created successfully with ' . $tender->documents()->count() . ' document(s).');
    }

    /**
     * Display the specified resource.
     */
    public function show(Tender $tender)
    {
        $tender->load([
            'creator' => function ($q) {
                $q->withTrashed()->select('id', 'name', 'email');
            },
            'bids' => function ($query) {
                $query->with('vendor:id,name,email,phone')
                    ->orderBy('bid_amount', 'asc');
            },
            'awardedBid.vendor:id,name,email,phone',
        ]);

        return Inertia::render('tenders/Show', [
            'tender' => $tender,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Tender $tender)
    {
        // Cannot edit awarded or cancelled tenders
        if (in_array($tender->status, ['Awarded', 'Cancelled'])) {
            return redirect()
                ->route('tenders.show', $tender)
                ->with('error', 'Cannot edit awarded or cancelled tenders.');
        }

        return Inertia::render('tenders/Edit', [
            'tender' => $tender,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Tender $tender)
    {
        // Cannot update awarded or cancelled tenders
        if (in_array($tender->status, ['Awarded', 'Cancelled'])) {
            return redirect()
                ->route('tenders.show', $tender)
                ->with('error', 'Cannot update awarded or cancelled tenders.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255', Rule::unique('tenders')->ignore($tender->id)],
            'description' => ['required', 'string'],
            'estimated_budget' => ['nullable', 'numeric', 'min:0'],
            'opening_date' => ['required', 'date'],
            'closing_date' => ['required', 'date', 'after:opening_date'],
            'status' => ['required', Rule::in(['Draft', 'Published', 'Closed', 'Cancelled'])],
            'requirements' => ['nullable', 'string'],
            'terms_conditions' => ['nullable', 'string'],
        ]);

        $tender->update($validated);

        return redirect()
            ->route('tenders.show', $tender)
            ->with('success', 'Tender updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Tender $tender)
    {
        // Cannot delete awarded tenders
        if ($tender->status === 'Awarded') {
            return redirect()
                ->route('tenders.index')
                ->with('error', 'Cannot delete awarded tenders.');
        }

        // Check if tender has any bids
        if ($tender->bids()->exists()) {
            return redirect()
                ->route('tenders.index')
                ->with('error', 'Cannot delete tender with existing bids.');
        }

        $tender->delete();

        return redirect()
            ->route('tenders.index')
            ->with('success', 'Tender deleted successfully.');
    }

    /**
     * Award tender to a specific bid
     */
    public function award(Request $request, Tender $tender)
    {
        $validated = $request->validate([
            'bid_id' => ['required', 'exists:tender_bids,id'],
        ]);

        $bid = TenderBid::findOrFail($validated['bid_id']);

        // Verify bid belongs to this tender
        if ($bid->tender_id !== $tender->id) {
            return redirect()
                ->route('tenders.show', $tender)
                ->with('error', 'Invalid bid for this tender.');
        }

        // Update tender
        $tender->update([
            'status' => 'Awarded',
            'awarded_bid_id' => $bid->id,
            'awarded_at' => now(),
        ]);

        // Update bid status
        $bid->markAsAccepted();

        // Reject other bids
        $tender->bids()
            ->where('id', '!=', $bid->id)
            ->where('status', '!=', 'Rejected')
            ->each(function ($otherBid) {
                $otherBid->markAsRejected('Another bid was selected');
            });

        return redirect()
            ->route('tenders.show', $tender)
            ->with('success', 'Tender awarded successfully.');
    }
}
