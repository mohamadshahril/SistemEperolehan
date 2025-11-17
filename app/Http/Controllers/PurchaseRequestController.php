<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\FileReference;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PurchaseRequestController extends Controller
{
    /**
     * Employee: My Purchase Requests listing
     */
    public function index(Request $request)
    {
        $user = $request->user();

        $validated = $request->validate([
            'search' => ['nullable', 'string'],
            'status' => ['nullable', 'string'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'sort_by' => ['nullable', Rule::in(['title', 'budget', 'submitted_at', 'status'])],
            'sort_dir' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = PurchaseRequest::query()->where('user_id', $user->id);

        // Search by title, purpose, ID, purchase_code, or date (submitted_at)
        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhere('id', $search)
                    ->orWhere('purchase_code', 'like', "%{$search}%")
                    ->orWhereDate('submitted_at', $search);
            });
        }

        // Filter by status
        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        // Date range filter
        if ($from = $request->date('from_date')) {
            $query->whereDate('submitted_at', '>=', $from);
        }
        if ($to = $request->date('to_date')) {
            $query->whereDate('submitted_at', '<=', $to);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'submitted_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = (int) $request->input('per_page', 10);
        $requests = $query->paginate($perPage)->withQueryString();

        return Inertia::render('purchase-requests/Index', [
            'requests' => $requests,
            'filters' => [
                'search' => $request->input('search'),
                'status' => $request->input('status'),
                'from_date' => $request->input('from_date'),
                'to_date' => $request->input('to_date'),
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
                'per_page' => $perPage,
            ],
            'statuses' => ['Pending', 'Approved', 'Rejected'],
        ]);
    }

    /**
     * Manager: View all pending purchase requests with filters
     */
    public function approvalsIndex(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string'],
            'employee' => ['nullable', 'string'], // legacy support
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'status' => ['nullable', 'string'],
            'sort_by' => ['nullable', Rule::in(['id', 'title', 'budget', 'submitted_at', 'status'])],
            'sort_dir' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = PurchaseRequest::query()
            ->with(['user:id,name,email']);

        // Status filter (default to Pending for backward compatibility)
        $statusParam = $request->string('status')->toString();
        $effectiveStatus = $statusParam !== '' ? $statusParam : 'Pending';
        if ($effectiveStatus !== '' && strtolower($effectiveStatus) !== 'all') {
            $query->where('status', $effectiveStatus);
        }

        // Unified search across ref id, employee (name/email), title, status, code and date
        if ($search = $request->string('search')->toString()) {
            $normalized = trim($search);
            // If starts with # treat as ID
            $idCandidate = ltrim($normalized, '#');
            $query->where(function ($q) use ($normalized, $idCandidate) {
                // Ref ID exact match
                if (ctype_digit($idCandidate)) {
                    $q->orWhere('id', (int) $idCandidate);
                }
                // Title
                $q->orWhere('title', 'like', "%{$normalized}%");
                // Status (even though this view is Pending, allow matching text)
                $q->orWhere('status', 'like', "%{$normalized}%");
                // Date (submitted_at) exact date match if looks like date
                // Allow formats like YYYY-MM-DD
                if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $normalized)) {
                    $q->orWhereDate('submitted_at', $normalized);
                }
                // Employee name/email
                $q->orWhereHas('user', function ($uq) use ($normalized) {
                    $uq->where('name', 'like', "%{$normalized}%")
                       ->orWhere('email', 'like', "%{$normalized}%");
                });
                // Purchase code search
                $q->orWhere('purchase_code', 'like', "%{$normalized}%");
            });
        } elseif ($employee = $request->string('employee')->toString()) { // legacy param
            $query->whereHas('user', function ($q) use ($employee) {
                $q->where('name', 'like', "%{$employee}%")
                  ->orWhere('email', 'like', "%{$employee}%");
            });
        }

        if ($from = $request->date('from_date')) {
            $query->whereDate('submitted_at', '>=', $from);
        }
        if ($to = $request->date('to_date')) {
            $query->whereDate('submitted_at', '<=', $to);
        }

        // Sorting (defaults)
        $sortBy = $request->input('sort_by', 'submitted_at');
        $sortDir = $request->input('sort_dir', 'desc');

        $perPage = (int) $request->input('per_page', 10);
        $requests = $query
            ->orderBy($sortBy, $sortDir)
            ->paginate($perPage)
            ->withQueryString();

        return Inertia::render('approvals/Index', [
            'requests' => $requests,
            'filters' => [
                'search' => $request->input('search'),
                'employee' => $request->input('employee'),
                'from_date' => $request->input('from_date'),
                'to_date' => $request->input('to_date'),
                'status' => $effectiveStatus,
                'sort_by' => $sortBy,
                'sort_dir' => $sortDir,
                'per_page' => $perPage,
            ],
            'statuses' => ['All', 'Pending', 'Approved', 'Rejected'],
        ]);
    }

    /**
     * Manager: View a single purchase request by ref id
     */
    public function approvalsShow(PurchaseRequest $purchaseRequest)
    {
        // Eager load minimal relations for display
        $purchaseRequest->load(['user:id,name,email']);

        return Inertia::render('approvals/Show', [
            'request' => $purchaseRequest,
        ]);
    }

    /**
     * Manager: Approve a pending purchase request with optional comment
     */
    public function approve(Request $request, PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'Pending') {
            return back()->with('error', 'Only pending requests can be approved.');
        }

        $data = $request->validate([
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $purchaseRequest->status = 'Approved';
        $purchaseRequest->approval_comment = $data['comment'] ?? null;
        $purchaseRequest->approved_by = $request->user()->id;
        $purchaseRequest->approved_at = now();
        $purchaseRequest->save();

        return back()->with('success', 'Purchase request approved.');
    }

    /**
     * Manager: Reject a pending purchase request with optional comment
     */
    public function reject(Request $request, PurchaseRequest $purchaseRequest)
    {
        if ($purchaseRequest->status !== 'Pending') {
            return back()->with('error', 'Only pending requests can be rejected.');
        }

        $data = $request->validate([
            'comment' => ['nullable', 'string', 'max:1000'],
        ]);

        $purchaseRequest->status = 'Rejected';
        $purchaseRequest->approval_comment = $data['comment'] ?? null;
        $purchaseRequest->approved_by = $request->user()->id;
        $purchaseRequest->approved_at = now();
        $purchaseRequest->save();

        return back()->with('success', 'Purchase request rejected.');
    }

    public function create()
    {
        // Load dropdown options
        $typeProcurements = DB::table('type_procurements')
            ->select('id', 'procurement_code', 'procurement_description')
            ->orderBy('procurement_code')
            ->get();
        $fileReferences = DB::table('file_references')
            ->select('id', 'file_code', 'file_description')
            ->orderBy('file_code')
            ->get();
        $vots = DB::table('vots')
            ->select('id', 'vot_code', 'vot_description')
            ->orderBy('vot_code')
            ->get();

        $user = auth()->user();
        return Inertia::render('purchase-requests/Create', [
            'options' => [
                'type_procurements' => $typeProcurements,
                'file_references' => $fileReferences,
                'vots' => $vots,
            ],
            'current_user' => [
                'name' => $user?->name,
                'location_iso_code' => $user?->location_iso_code,
            ],
            'today' => now()->toDateString(),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type_procurement_id' => ['required', 'integer', 'exists:type_procurements,id'],
            'file_reference_id' => ['required', 'integer', 'exists:file_references,id'],
            'vot_id' => ['required', 'integer', 'exists:vots,id'],
            'budget' => ['required', 'numeric', 'min:0'],
            'purpose' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_no' => ['required', 'integer', 'min:1'],
            'items.*.details' => ['required', 'string', 'max:500'],
            'items.*.purpose' => ['nullable', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:5120'],
        ]);

        // Enforce per-item price must not exceed the budget
        foreach ($validated['items'] as $idx => $item) {
            if ((float)$item['price'] > (float)$validated['budget']) {
                return back()->withErrors(["items.$idx.price" => 'Item price must not exceed the budget.'])->withInput();
            }
        }

        // Enforce total cost must not exceed the budget
        $total = 0.0;
        foreach ($validated['items'] as $item) {
            $qty = (int)$item['quantity'];
            $price = (float)$item['price'];
            $total += $qty * $price;
        }
        if ($total > (float)$validated['budget']) {
            return back()->withErrors(['budget' => 'Total items cost (RM ' . number_format($total, 2) . ') must not exceed the budget.'])->withInput();
        }

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('purchase_requests', 'public');
        }

        $user = $request->user();
        $purchaseRequest = new PurchaseRequest();
        $purchaseRequest->user_id = $user->id;
        $purchaseRequest->title = $validated['title'];
        $purchaseRequest->type_procurement_id = $validated['type_procurement_id'];
        $purchaseRequest->file_reference_id = $validated['file_reference_id'];
        $purchaseRequest->vot_id = $validated['vot_id'];
        $purchaseRequest->location_iso_code = $user->location_iso_code ?? '';
        $purchaseRequest->budget = $validated['budget'];
        $purchaseRequest->purpose = $validated['purpose'] ?? null;
        $purchaseRequest->items = $validated['items'];
        $purchaseRequest->status = 'Pending';
        $purchaseRequest->submitted_at = now();
        $purchaseRequest->attachment_path = $path;
        $purchaseRequest->save();

        // Generate purchase code after we have an ID
        $purchaseRequest->purchase_code = $this->generatePurchaseCode($purchaseRequest);
        $purchaseRequest->save();

        return redirect()
            ->route('purchase-requests.index')
            ->with('success', 'Purchase request submitted for approval.');
    }

    public function edit(Request $request, PurchaseRequest $purchaseRequest)
    {
        // Ownership check
        abort_if($purchaseRequest->user_id !== $request->user()->id, 403);
        // Load dropdown options similar to create
        $typeProcurements = DB::table('type_procurements')
            ->select('id', 'procurement_code', 'procurement_description')
            ->orderBy('procurement_code')
            ->get();
        $fileReferences = DB::table('file_references')
            ->select('id', 'file_code', 'file_description')
            ->orderBy('file_code')
            ->get();
        $vots = DB::table('vots')
            ->select('id', 'vot_code', 'vot_description')
            ->orderBy('vot_code')
            ->get();

        return Inertia::render('purchase-requests/Edit', [
            'request' => [
                'id' => $purchaseRequest->id,
                'title' => $purchaseRequest->title,
                'type_procurement_id' => $purchaseRequest->type_procurement_id,
                'file_reference_id' => $purchaseRequest->file_reference_id,
                'vot_id' => $purchaseRequest->vot_id,
                'location_iso_code' => $purchaseRequest->location_iso_code,
                'budget' => $purchaseRequest->budget,
                'items' => $purchaseRequest->items,
                'purpose' => $purchaseRequest->purpose,
                'status' => $purchaseRequest->status,
                'submitted_at' => $purchaseRequest->submitted_at,
                'attachment_path' => $purchaseRequest->attachment_path,
                'attachment_url' => $purchaseRequest->attachment_path ? Storage::disk('public')->url($purchaseRequest->attachment_path) : null,
                'purchase_code' => $purchaseRequest->purchase_code,
            ],
            'canEdit' => $purchaseRequest->status === 'Pending',
            'options' => [
                'type_procurements' => $typeProcurements,
                'file_references' => $fileReferences,
                'vots' => $vots,
            ],
        ]);
    }

    /**
     * Show a single purchase request in read-only mode for the owner
     */
    public function show(Request $request, PurchaseRequest $purchaseRequest)
    {
        // Ownership check
        abort_if($purchaseRequest->user_id !== $request->user()->id, 403);

        return Inertia::render('purchase-requests/Show', [
            'request' => [
                'id' => $purchaseRequest->id,
                'title' => $purchaseRequest->title,
                'type_procurement_id' => $purchaseRequest->type_procurement_id,
                'file_reference_id' => $purchaseRequest->file_reference_id,
                'vot_id' => $purchaseRequest->vot_id,
                'location_iso_code' => $purchaseRequest->location_iso_code,
                'budget' => $purchaseRequest->budget,
                'items' => $purchaseRequest->items,
                'purpose' => $purchaseRequest->purpose,
                'status' => $purchaseRequest->status,
                'submitted_at' => $purchaseRequest->submitted_at,
                'attachment_path' => $purchaseRequest->attachment_path,
                'attachment_url' => $purchaseRequest->attachment_path ? Storage::disk('public')->url($purchaseRequest->attachment_path) : null,
                'purchase_code' => $purchaseRequest->purchase_code,
            ],
        ]);
    }

    public function update(Request $request, PurchaseRequest $purchaseRequest)
    {
        // Ownership and status check
        abort_if($purchaseRequest->user_id !== $request->user()->id, 403);
        if ($purchaseRequest->status !== 'Pending') {
            return redirect()->route('purchase-requests.index')
                ->with('error', 'Only pending requests can be edited.');
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type_procurement_id' => ['required', 'integer', 'exists:type_procurements,id'],
            'file_reference_id' => ['required', 'integer', 'exists:file_references,id'],
            'vot_id' => ['required', 'integer', 'exists:vots,id'],
            'budget' => ['required', 'numeric', 'min:0'],
            'purpose' => ['nullable', 'string', 'max:1000'],
            'items' => ['required', 'array', 'min:1'],
            'items.*.item_no' => ['required', 'integer', 'min:1'],
            'items.*.details' => ['required', 'string', 'max:500'],
            'items.*.purpose' => ['nullable', 'string', 'max:500'],
            'items.*.quantity' => ['required', 'integer', 'min:1'],
            'items.*.price' => ['required', 'numeric', 'min:0'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:5120'],
        ]);

        foreach ($validated['items'] as $idx => $item) {
            if ((float)$item['price'] > (float)$validated['budget']) {
                return back()->withErrors(["items.$idx.price" => 'Item price must not exceed the budget.'])->withInput();
            }
        }

        // Enforce total cost must not exceed the budget
        $total = 0.0;
        foreach ($validated['items'] as $item) {
            $qty = (int)$item['quantity'];
            $price = (float)$item['price'];
            $total += $qty * $price;
        }
        if ($total > (float)$validated['budget']) {
            return back()->withErrors(['budget' => 'Total items cost (RM ' . number_format($total, 2) . ') must not exceed the budget.'])->withInput();
        }

        // Handle optional attachment replacement
        if ($request->hasFile('attachment')) {
            if ($purchaseRequest->attachment_path && Storage::disk('public')->exists($purchaseRequest->attachment_path)) {
                Storage::disk('public')->delete($purchaseRequest->attachment_path);
            }
            $purchaseRequest->attachment_path = $request->file('attachment')->store('purchase_requests', 'public');
        }

        $purchaseRequest->title = $validated['title'];
        $purchaseRequest->type_procurement_id = $validated['type_procurement_id'];
        $purchaseRequest->file_reference_id = $validated['file_reference_id'];
        $purchaseRequest->vot_id = $validated['vot_id'];
        $purchaseRequest->budget = $validated['budget'];
        $purchaseRequest->purpose = $validated['purpose'] ?? null;
        $purchaseRequest->items = $validated['items'];
        $purchaseRequest->save();

        return redirect()
            ->route('purchase-requests.index')
            ->with('success', 'Purchase request updated successfully.');
    }

    public function destroy(Request $request, PurchaseRequest $purchaseRequest)
    {
        // Ownership and status check
        abort_if($purchaseRequest->user_id !== $request->user()->id, 403);
        if ($purchaseRequest->status !== 'Pending') {
            return redirect()->route('purchase-requests.index')
                ->with('error', 'Only pending requests can be deleted.');
        }

        // Delete attachment if exists
        if ($purchaseRequest->attachment_path && Storage::disk('public')->exists($purchaseRequest->attachment_path)) {
            Storage::disk('public')->delete($purchaseRequest->attachment_path);
        }

        $purchaseRequest->delete();

        return redirect()
            ->route('purchase-requests.index')
            ->with('success', 'Purchase request deleted successfully.');
    }

    /**
     * Generate a purchase code in format: AIM/BDGT({location})/{file_code}/{vot_code}/{running}
     * Example: AIM/BDGT/MY-SGR/400-11/232/1
     */
    protected function generatePurchaseCode(PurchaseRequest $pr): string
    {
        // Location from captured iso code on the request
        $locationPart = $pr->location_iso_code ?: 'LOC';

        // File reference code
        $fileCode = FileReference::query()->whereKey($pr->file_reference_id)->value('file_code') ?? 'FILE';
        // Vot code
        $votCode = DB::table('vots')->where('id', $pr->vot_id)->value('vot_code') ?? 'VOT';

        // Running number per composite key (location + file + vot)
        $running = (int) DB::table('purchase_requests')
            ->where('location_iso_code', $pr->location_iso_code)
            ->where('file_reference_id', $pr->file_reference_id)
            ->where('vot_id', $pr->vot_id)
            ->count();
        // Next number
        $running += 1;

        // Include BDGT and location per requirement
        return sprintf('AIM/BDGT/%s/%s/%s/%d', $locationPart, $fileCode, $votCode, $running);
    }
}
