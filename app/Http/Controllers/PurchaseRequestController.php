<?php

namespace App\Http\Controllers;

use App\Models\PurchaseRequest;
use App\Models\FileReference;
use App\Models\Status;
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
            'sort_by' => ['nullable', Rule::in(['id', 'title', 'budget', 'submitted_at', 'status'])],
            'sort_dir' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = PurchaseRequest::query()
            ->where('user_id', $user->id)
            ->with(['statusRef:id,name']);

        // Search by title, note (formerly purpose), ID, purchase_ref_no, or date (submitted_at)
        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('note', 'like', "%{$search}%")
                    ->orWhere('id', $search)
                    ->orWhere('purchase_ref_no', 'like', "%{$search}%")
                    ->orWhereDate('submitted_at', $search);
            });
        }

        // Filter by status (name -> id). Default to 'Pending' when not provided.
        $effectiveStatus = $request->filled('status')
            ? $request->string('status')->toString()
            : 'Pending';

        if ($effectiveStatus !== '') {
            $statusId = Status::query()->where('name', $effectiveStatus)->value('id');
            if ($statusId) {
                $query->where('status_id', $statusId);
            } else {
                // If unknown status name provided, ensure no results rather than error
                $query->whereRaw('1=0');
            }
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
        if ($sortBy === 'status') {
            $sortBy = 'status_id';
        }
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = (int) $request->input('per_page', 10);
        $requests = $query->paginate($perPage)->withQueryString();

        return Inertia::render('purchase-requests/Index', [
            'requests' => $requests,
            'filters' => [
                'search' => $request->input('search'),
                // Reflect the effective status so UI shows default 'Pending' when not provided
                'status' => $request->filled('status') ? $request->input('status') : 'Pending',
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
            ->with(['user:id,name,email', 'statusRef:id,name']);

        // Status filter (default to Pending for backward compatibility)
        $statusParam = $request->string('status')->toString();
        $effectiveStatus = $statusParam !== '' ? $statusParam : 'Pending';
        if ($effectiveStatus !== '' && strtolower($effectiveStatus) !== 'all') {
            $statusId = Status::query()->where('name', $effectiveStatus)->value('id');
            if ($statusId) {
                $query->where('status_id', $statusId);
            } else {
                $query->whereRaw('1=0');
            }
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
                // Status name match
                $q->orWhereHas('statusRef', function ($sq) use ($normalized) {
                    $sq->where('name', 'like', "%{$normalized}%");
                });
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
                // Purchase reference no search
                $q->orWhere('purchase_ref_no', 'like', "%{$normalized}%");
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
        if ($sortBy === 'status') {
            $sortBy = 'status_id';
        }
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
        $purchaseRequest->approval_remarks = $data['comment'] ?? null;
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
        $purchaseRequest->approval_remarks = $data['comment'] ?? null;
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
            // Canonical field is `note`; accept legacy `purpose` for backward compatibility
            'note' => ['nullable', 'string', 'max:1000'],
            'purpose' => ['nullable', 'string', 'max:1000'],
            'item' => ['required', 'array', 'min:1'],
            'item.*.details' => ['required', 'string', 'max:500'],
            'item.*.purpose' => ['nullable', 'string', 'max:500'],
            // Keep optional fields so they persist to DB when provided from the UI
            'item.*.item_code' => ['nullable', 'string', 'max:100'],
            'item.*.unit' => ['nullable', 'string', 'max:50'],
            'item.*.quantity' => ['required', 'integer', 'min:1'],
            'item.*.price' => ['required', 'numeric', 'min:0'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:5120'],
        ]);

        // Enforce per-item price must not exceed the budget
        foreach ($validated['item'] as $idx => $item) {
            if ((float)$item['price'] > (float)$validated['budget']) {
                return back()->withErrors(["item.$idx.price" => 'Item price must not exceed the budget.'])->withInput();
            }
        }

        // Enforce total cost must not exceed the budget
        $total = 0.0;
        foreach ($validated['item'] as $item) {
            $qty = (int)$item['quantity'];
            $price = (float)$item['price'];
            $total += $qty * $price;
        }
        if ($total > (float)$validated['budget']) {
            return back()->withErrors(['budget' => 'Total item cost (RM ' . number_format($total, 2) . ') must not exceed the budget.'])->withInput();
        }

        $path = null;
        if ($request->hasFile('attachment')) {
            $path = $request->file('attachment')->store('purchase_requests', 'public');
        }

        $user = $request->user();
        // Initialize variable to be captured by reference inside the transaction
        $purchaseRequest = null;

        DB::transaction(function () use ($validated, $user, $path, &$purchaseRequest) {
            $purchaseRequest = new PurchaseRequest();
            $purchaseRequest->user_id = $user->id;
            // Ensure user has a staff_id; if missing, generate a simple one and persist
            if (empty($user->staff_id)) {
                $user->staff_id = 'U' . str_pad((string) $user->id, 5, '0', STR_PAD_LEFT);
                $user->save();
            }
            // applicant_id now stores staff_id instead of user_id
            $purchaseRequest->applicant_id = $user->staff_id;
            $purchaseRequest->title = $validated['title'];
            $purchaseRequest->type_procurement_id = $validated['type_procurement_id'];
            $purchaseRequest->file_reference_id = $validated['file_reference_id'];
            $purchaseRequest->vot_id = $validated['vot_id'];
            $purchaseRequest->location_iso_code = $user->location_iso_code ?? '';
            $purchaseRequest->budget = $validated['budget'];
            // Normalize note: prefer `note`, fallback to legacy `purpose`
            $normalizedNotes = $validated['note'] ?? $validated['purpose'] ?? null;
            $purchaseRequest->note = $normalizedNotes;
            $purchaseRequest->status = 'Pending';
            $purchaseRequest->submitted_at = now();
            $purchaseRequest->attachment_path = $path;
            $purchaseRequest->save();

            // Persist related items into purchase_items table
            foreach ($validated['item'] as $row) {
                $purchaseRequest->items()->create([
                    'item_name'   => $row['details'],
                    'item_code'   => $row['item_code'] ?? null,
                    'purpose'     => $row['purpose'] ?? null,
                    'unit'        => $row['unit'] ?? null,
                    'quantity'    => $row['quantity'],
                    'unit_price'  => $row['price'],
                    'total_price' => ($row['quantity'] ?? 0) * ($row['price'] ?? 0),
                ]);
            }

            // Generate purchase reference no after we have an ID
            $purchaseRequest->purchase_ref_no = $this->generatePurchaseRefNo($purchaseRequest);
            $purchaseRequest->save();
        });

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
                // Provide items from relation in both legacy and new shapes
                'item' => $purchaseRequest->items()->get()->map(function ($it) {
                    return [
                        'item_no' => null, // UI can compute index
                        'details' => $it->item_name,
                        'purpose' => $it->purpose,
                        'quantity' => $it->quantity,
                        'price' => $it->unit_price,
                        'item_code' => $it->item_code,
                        'unit' => $it->unit,
                    ];
                }),
                'items' => $purchaseRequest->items()->get()->map(function ($it) {
                    return [
                        'item_no' => null,
                        'details' => $it->item_name,
                        'purpose' => $it->purpose,
                        'quantity' => $it->quantity,
                        'price' => $it->unit_price,
                        'item_code' => $it->item_code,
                        'unit' => $it->unit,
                    ];
                }),
                // Expose both for a transitional period; frontend can migrate to `note`
                'note' => $purchaseRequest->note,
                'purpose' => $purchaseRequest->purpose,
                'status' => $purchaseRequest->status,
                'submitted_at' => $purchaseRequest->submitted_at,
                'attachment_path' => $purchaseRequest->attachment_path,
                'attachment_url' => $purchaseRequest->attachment_path ? Storage::disk('public')->url($purchaseRequest->attachment_path) : null,
                'purchase_ref_no' => $purchaseRequest->purchase_ref_no,
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

        // Load single-option lists so the read-only form can render selected labels
        $typeProcurements = DB::table('type_procurements')
            ->select('id', 'procurement_code', 'procurement_description')
            ->where('id', $purchaseRequest->type_procurement_id)
            ->get();
        $fileReferences = DB::table('file_references')
            ->select('id', 'file_code', 'file_description')
            ->where('id', $purchaseRequest->file_reference_id)
            ->get();
        $vots = DB::table('vots')
            ->select('id', 'vot_code', 'vot_description')
            ->where('id', $purchaseRequest->vot_id)
            ->get();

        return Inertia::render('purchase-requests/Show', [
            'request' => [
                'id' => $purchaseRequest->id,
                'title' => $purchaseRequest->title,
                'type_procurement_id' => $purchaseRequest->type_procurement_id,
                'file_reference_id' => $purchaseRequest->file_reference_id,
                'vot_id' => $purchaseRequest->vot_id,
                'location_iso_code' => $purchaseRequest->location_iso_code,
                'budget' => $purchaseRequest->budget,
                'item' => $purchaseRequest->items()->get()->map(function ($it) {
                    return [
                        'item_no' => null,
                        'details' => $it->item_name,
                        'purpose' => $it->purpose,
                        'quantity' => $it->quantity,
                        'price' => $it->unit_price,
                        'item_code' => $it->item_code,
                        'unit' => $it->unit,
                    ];
                }),
                'items' => $purchaseRequest->items()->get()->map(function ($it) {
                    return [
                        'item_no' => null,
                        'details' => $it->item_name,
                        'purpose' => $it->purpose,
                        'quantity' => $it->quantity,
                        'price' => $it->unit_price,
                        'item_code' => $it->item_code,
                        'unit' => $it->unit,
                    ];
                }),
                'note' => $purchaseRequest->note,
                'purpose' => $purchaseRequest->purpose,
                'status' => $purchaseRequest->status,
                'submitted_at' => $purchaseRequest->submitted_at,
                'attachment_path' => $purchaseRequest->attachment_path,
                'attachment_url' => $purchaseRequest->attachment_path ? Storage::disk('public')->url($purchaseRequest->attachment_path) : null,
                'purchase_ref_no' => $purchaseRequest->purchase_ref_no,
            ],
            'options' => [
                'type_procurements' => $typeProcurements,
                'file_references' => $fileReferences,
                'vots' => $vots,
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

        // Normalize payload: accept either `item` or `items` from the frontend
        // Frontend Edit page may submit `items`, while Create uses `item`.
        if (!$request->has('item') && $request->has('items')) {
            $request->merge(['item' => $request->input('items')]);
        }

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'type_procurement_id' => ['required', 'integer', 'exists:type_procurements,id'],
            'file_reference_id' => ['required', 'integer', 'exists:file_references,id'],
            'vot_id' => ['required', 'integer', 'exists:vots,id'],
            'budget' => ['required', 'numeric', 'min:0'],
            // Keep the same rules as store() for consistency
            'note' => ['nullable', 'string', 'max:1000'],
            'purpose' => ['nullable', 'string', 'max:1000'],
            'item' => ['required', 'array', 'min:1'],
            'item.*.details' => ['required', 'string', 'max:500'],
            'item.*.purpose' => ['nullable', 'string', 'max:500'],
            'item.*.item_code' => ['nullable', 'string', 'max:100'],
            'item.*.unit' => ['nullable', 'string', 'max:50'],
            'item.*.quantity' => ['required', 'integer', 'min:1'],
            'item.*.price' => ['required', 'numeric', 'min:0'],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:5120'],
        ]);

        foreach ($validated['item'] as $idx => $item) {
            if ((float)$item['price'] > (float)$validated['budget']) {
                return back()->withErrors(["item.$idx.price" => 'Item price must not exceed the budget.'])->withInput();
            }
        }

        // Enforce total cost must not exceed the budget
        $total = 0.0;
        foreach ($validated['item'] as $item) {
            $qty = (int)$item['quantity'];
            $price = (float)$item['price'];
            $total += $qty * $price;
        }
        if ($total > (float)$validated['budget']) {
            return back()->withErrors(['budget' => 'Total item cost (RM ' . number_format($total, 2) . ') must not exceed the budget.'])->withInput();
        }

        DB::transaction(function () use ($request, $purchaseRequest, $validated) {
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
            $normalizedNotes = $validated['note'] ?? $validated['purpose'] ?? null;
            $purchaseRequest->note = $normalizedNotes;
            $purchaseRequest->save();

            // Sync items: simple strategy delete then recreate
            $purchaseRequest->items()->delete();
            foreach ($validated['item'] as $row) {
                $purchaseRequest->items()->create([
                    'item_name'   => $row['details'],
                    'item_code'   => $row['item_code'] ?? null,
                    'purpose'     => $row['purpose'] ?? null,
                    'unit'        => $row['unit'] ?? null,
                    'quantity'    => $row['quantity'],
                    'unit_price'  => $row['price'],
                    'total_price' => ($row['quantity'] ?? 0) * ($row['price'] ?? 0),
                ]);
            }
        });

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
    protected function generatePurchaseRefNo(PurchaseRequest $pr): string
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
        return sprintf('AIM/%s/%s/%s/%d', $locationPart, $fileCode, $votCode, $running);
    }
}
