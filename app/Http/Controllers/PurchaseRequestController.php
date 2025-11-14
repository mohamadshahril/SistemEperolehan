<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
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
            'sort_by' => ['nullable', Rule::in(['item_name', 'quantity', 'price', 'submitted_at', 'status'])],
            'sort_dir' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = PurchaseRequest::query()->where('user_id', $user->id);

        // Search by item name, purpose, ID, or date (submitted_at)
        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('item_name', 'like', "%{$search}%")
                    ->orWhere('purpose', 'like', "%{$search}%")
                    ->orWhere('id', $search)
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
            'sort_by' => ['nullable', Rule::in(['id', 'item_name', 'quantity', 'price', 'submitted_at', 'status'])],
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

        // Unified search across ref id, employee (name/email), item, status, and date
        if ($search = $request->string('search')->toString()) {
            $normalized = trim($search);
            // If starts with # treat as ID
            $idCandidate = ltrim($normalized, '#');
            $query->where(function ($q) use ($normalized, $idCandidate) {
                // Ref ID exact match
                if (ctype_digit($idCandidate)) {
                    $q->orWhere('id', (int) $idCandidate);
                }
                // Item name
                $q->orWhere('item_name', 'like', "%{$normalized}%");
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
        return Inertia::render('purchase-requests/Create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'item_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'purpose' => ['nullable', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:5120'],
        ]);

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('purchase_requests', 'public');
        }

        $purchaseRequest = PurchaseRequest::create([
            'user_id' => Auth::id(),
            'item_name' => $validated['item_name'],
            'quantity' => $validated['quantity'],
            'price' => $validated['price'],
            'purpose' => $validated['purpose'] ?? null,
            'status' => 'Pending',
            'submitted_at' => now(),
            'attachment_path' => $path,
        ]);

        return redirect()
            ->route('purchase-requests.index')
            ->with('success', 'Purchase request submitted for approval.');
    }

    public function edit(Request $request, PurchaseRequest $purchaseRequest)
    {
        // Ownership check
        abort_if($purchaseRequest->user_id !== $request->user()->id, 403);

        return Inertia::render('purchase-requests/Edit', [
            'request' => [
                'id' => $purchaseRequest->id,
                'item_name' => $purchaseRequest->item_name,
                'quantity' => $purchaseRequest->quantity,
                'price' => $purchaseRequest->price,
                'purpose' => $purchaseRequest->purpose,
                'status' => $purchaseRequest->status,
                'submitted_at' => $purchaseRequest->submitted_at,
                'attachment_path' => $purchaseRequest->attachment_path,
                'attachment_url' => $purchaseRequest->attachment_path ? Storage::disk('public')->url($purchaseRequest->attachment_path) : null,
            ],
            'canEdit' => $purchaseRequest->status === 'Pending',
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
            'item_name' => ['required', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'price' => ['required', 'numeric', 'min:0'],
            'purpose' => ['nullable', 'string', 'max:1000'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:5120'],
        ]);

        // Handle optional attachment replacement
        if ($request->hasFile('attachment')) {
            if ($purchaseRequest->attachment_path && Storage::disk('public')->exists($purchaseRequest->attachment_path)) {
                Storage::disk('public')->delete($purchaseRequest->attachment_path);
            }
            $purchaseRequest->attachment_path = $request->file('attachment')->store('purchase_requests', 'public');
        }

        $purchaseRequest->item_name = $validated['item_name'];
        $purchaseRequest->quantity = $validated['quantity'];
        $purchaseRequest->price = $validated['price'];
        $purchaseRequest->purpose = $validated['purpose'] ?? null;
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
}
