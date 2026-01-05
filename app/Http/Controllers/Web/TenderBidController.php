<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\Tender;
use App\Models\TenderBid;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class TenderBidController extends Controller
{
    /**
     * Display a listing of tender bids (for vendors to view available tenders)
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['Draft', 'Published', 'Closed', 'Awarded', 'Cancelled'])],
            'year' => ['nullable', 'integer', 'min:2000', 'max:' . (date('Y') + 10)],
            'opening_date_from' => ['nullable', 'date'],
            'opening_date_to' => ['nullable', 'date', 'after_or_equal:opening_date_from'],
            'closing_date_from' => ['nullable', 'date'],
            'closing_date_to' => ['nullable', 'date', 'after_or_equal:closing_date_from'],
            'budget_min' => ['nullable', 'numeric', 'min:0'],
            'budget_max' => ['nullable', 'numeric', 'min:0', 'gte:budget_min'],
            'quick_filter' => ['nullable', Rule::in(['open_now', 'closing_soon', 'recently_added'])],
            'sort_by' => ['nullable', Rule::in(['tender_number', 'title', 'closing_date', 'created_at', 'estimated_budget'])],
            'sort_dir' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = Tender::query()
            ->withCount('bids')
            ->with(['creator:id,name']);

        // Search
        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('tender_number', 'like', "%{$search}%")
                    ->orWhere('title', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by status (default to Published for vendors)
        $status = $request->input('status', 'Published');
        if ($status) {
            $query->where('status', $status);
        }

        // Filter by year (based on created_at)
        if ($year = $request->input('year')) {
            $query->whereYear('created_at', $year);
        }

        // Quick filters
        if ($quickFilter = $request->input('quick_filter')) {
            switch ($quickFilter) {
                case 'open_now':
                    $query->where('status', 'Published')
                        ->whereDate('opening_date', '<=', now())
                        ->whereDate('closing_date', '>=', now());
                    break;
                case 'closing_soon':
                    $query->where('status', 'Published')
                        ->whereDate('closing_date', '>=', now())
                        ->whereDate('closing_date', '<=', now()->addDays(7));
                    break;
                case 'recently_added':
                    $query->where('created_at', '>=', now()->subDays(7));
                    break;
            }
        }

        // Filter by opening date range
        if ($openingDateFrom = $request->input('opening_date_from')) {
            $query->whereDate('opening_date', '>=', $openingDateFrom);
        }
        if ($openingDateTo = $request->input('opening_date_to')) {
            $query->whereDate('opening_date', '<=', $openingDateTo);
        }

        // Filter by closing date range
        if ($closingDateFrom = $request->input('closing_date_from')) {
            $query->whereDate('closing_date', '>=', $closingDateFrom);
        }
        if ($closingDateTo = $request->input('closing_date_to')) {
            $query->whereDate('closing_date', '<=', $closingDateTo);
        }

        // Filter by budget range
        if ($budgetMin = $request->input('budget_min')) {
            $query->where('estimated_budget', '>=', $budgetMin);
        }
        if ($budgetMax = $request->input('budget_max')) {
            $query->where('estimated_budget', '<=', $budgetMax);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'closing_date');
        $sortDir = $request->input('sort_dir', 'asc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = (int) $request->input('per_page', 10);
        $tenders = $query->paginate($perPage)->withQueryString();

        // Get all vendors for the bid submission form
        $vendors = Vendor::select('id', 'name', 'email')->orderBy('name')->get();

        return Inertia::render('tender-bids/Index', [
            'tenders' => $tenders,
            'vendors' => $vendors,
            'filters' => [
                'search' => $request->input('search'),
                'status' => $request->input('status'),
                'year' => $request->input('year'),
                'opening_date_from' => $request->input('opening_date_from'),
                'opening_date_to' => $request->input('opening_date_to'),
                'closing_date_from' => $request->input('closing_date_from'),
                'closing_date_to' => $request->input('closing_date_to'),
                'budget_min' => $request->input('budget_min'),
                'budget_max' => $request->input('budget_max'),
                'quick_filter' => $request->input('quick_filter'),
                'sort_by' => $request->input('sort_by'),
                'sort_dir' => $request->input('sort_dir'),
                'per_page' => $request->input('per_page'),
            ],
        ]);
    }

    /**
     * Store a newly created bid
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'tender_id' => ['required', 'exists:tenders,id'],
            'vendor_id' => ['required', 'exists:vendors,id'],
            'bid_amount' => ['required', 'numeric', 'min:0'],
            'proposal' => ['nullable', 'string'],
            'technical_specifications' => ['nullable', 'string'],
            'delivery_timeline_days' => ['nullable', 'integer', 'min:1'],
            'attachment' => ['nullable', 'file', 'max:10240', 'mimes:pdf,doc,docx,xls,xlsx,jpg,jpeg,png'],
        ]);

        $tender = Tender::findOrFail($validated['tender_id']);

        // Check if tender is open for bidding
        if (!$tender->isOpen()) {
            return redirect()
                ->route('tender-bids.index')
                ->with('error', 'This tender is not open for bidding.');
        }

        // Check if vendor already has a bid for this tender
        $existingBid = TenderBid::where('tender_id', $validated['tender_id'])
            ->where('vendor_id', $validated['vendor_id'])
            ->first();

        if ($existingBid) {
            return redirect()
                ->route('tender-bids.index')
                ->with('error', 'You have already submitted a bid for this tender.');
        }

        // Handle file upload
        if ($request->hasFile('attachment')) {
            $file = $request->file('attachment');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('tender-bids', $filename, 'public');
            $validated['attachment_path'] = $path;
        }

        $validated['status'] = 'Submitted';
        $validated['submitted_at'] = now();

        TenderBid::create($validated);

        return redirect()
            ->route('tender-bids.index')
            ->with('success', 'Bid submitted successfully.');
    }

    /**
     * Display the specified bid
     */
    public function show(TenderBid $tenderBid)
    {
        $tenderBid->load(['tender', 'vendor']);

        return Inertia::render('tender-bids/Show', [
            'bid' => $tenderBid,
        ]);
    }

    /**
     * Update the specified bid
     */
    public function update(Request $request, TenderBid $tenderBid)
    {
        // Can only update if bid is still in Submitted status
        if ($tenderBid->status !== 'Submitted') {
            return redirect()
                ->route('tender-bids.index')
                ->with('error', 'Cannot update bid that is already under review or processed.');
        }

        // Check if tender is still open
        if (!$tenderBid->tender->isOpen()) {
            return redirect()
                ->route('tender-bids.index')
                ->with('error', 'Cannot update bid for closed tender.');
        }

        $validated = $request->validate([
            'bid_amount' => ['required', 'numeric', 'min:0'],
            'proposal' => ['nullable', 'string'],
            'technical_specifications' => ['nullable', 'string'],
            'delivery_timeline_days' => ['nullable', 'integer', 'min:1'],
        ]);

        $tenderBid->update($validated);

        return redirect()
            ->route('tender-bids.index')
            ->with('success', 'Bid updated successfully.');
    }

    /**
     * Withdraw the bid
     */
    public function destroy(TenderBid $tenderBid)
    {
        // Can only withdraw if bid is in Submitted or Under Review status
        if (!in_array($tenderBid->status, ['Submitted', 'Under Review'])) {
            return redirect()
                ->route('tender-bids.index')
                ->with('error', 'Cannot withdraw bid that has been accepted or rejected.');
        }

        $tenderBid->markAsWithdrawn();

        return redirect()
            ->route('tender-bids.index')
            ->with('success', 'Bid withdrawn successfully.');
    }
}
