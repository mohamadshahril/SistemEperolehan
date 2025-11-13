<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $validated = $request->validate([
            'search' => ['nullable', 'string'],
            'status' => ['nullable', Rule::in(['Pending', 'Approved', 'Completed'])],
            'vendor_id' => ['nullable', 'exists:vendors,id'],
            'from_date' => ['nullable', 'date'],
            'to_date' => ['nullable', 'date', 'after_or_equal:from_date'],
            'sort_by' => ['nullable', Rule::in(['order_number', 'status', 'created_at'])],
            'sort_dir' => ['nullable', Rule::in(['asc', 'desc'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'per_page' => ['nullable', 'integer', 'min:1', 'max:100'],
        ]);

        $query = PurchaseOrder::query()->with('vendor');

        // Search by order number or details
        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%")
                    ->orWhere('id', $search);
            });
        }

        // Filter by status
        if ($status = $request->string('status')->toString()) {
            $query->where('status', $status);
        }

        // Filter by vendor
        if ($vendorId = $request->input('vendor_id')) {
            $query->where('vendor_id', $vendorId);
        }

        // Date range filter
        if ($from = $request->date('from_date')) {
            $query->whereDate('created_at', '>=', $from);
        }
        if ($to = $request->date('to_date')) {
            $query->whereDate('created_at', '<=', $to);
        }

        // Sorting
        $sortBy = $request->input('sort_by', 'created_at');
        $sortDir = $request->input('sort_dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        // Pagination
        $perPage = (int) $request->input('per_page', 10);
        $purchaseOrders = $query->paginate($perPage)->withQueryString();

        // Get all vendors for the filter dropdown
        $vendors = Vendor::orderBy('name')->get(['id', 'name']);

        return Inertia::render('purchase-orders/Index', [
            'purchaseOrders' => $purchaseOrders,
            'vendors' => $vendors,
            'filters' => [
                'search' => $request->input('search'),
                'status' => $request->input('status'),
                'vendor_id' => $request->input('vendor_id'),
                'from_date' => $request->input('from_date'),
                'to_date' => $request->input('to_date'),
                'sort_by' => $request->input('sort_by'),
                'sort_dir' => $request->input('sort_dir'),
                'per_page' => $request->input('per_page'),
            ],
            'statuses' => ['Pending', 'Approved', 'Completed'],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $vendors = Vendor::orderBy('name')->get(['id', 'name']);

        return Inertia::render('purchase-orders/Create', [
            'vendors' => $vendors,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'details' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['Pending', 'Approved', 'Completed'])],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:5120'],
        ]);

        // Generate unique order number
        $date = now()->format('Ymd');
        
        // Count all orders created today (including soft-deleted) to get the next sequence
        $count = PurchaseOrder::withTrashed()
            ->whereDate('created_at', now()->toDateString())
            ->count();
        
        $sequence = $count + 1;
        $orderNumber = sprintf('PO-%s-%04d', $date, $sequence);

        $validated['order_number'] = $orderNumber;

        // Handle file upload
        if ($request->hasFile('attachment')) {
            $validated['attachment_path'] = $request->file('attachment')->store('purchase_orders', 'public');
        }

        PurchaseOrder::create($validated);

        return redirect()
            ->route('purchase-orders.index')
            ->with('success', 'Purchase order created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        return Inertia::render('purchase-orders/Show', [
            'purchaseOrder' => $purchaseOrder->load('vendor'),
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        $vendors = Vendor::orderBy('name')->get(['id', 'name']);

        return Inertia::render('purchase-orders/Edit', [
            'purchaseOrder' => $purchaseOrder->load('vendor'),
            'vendors' => $vendors,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        $validated = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'details' => ['nullable', 'string', 'max:1000'],
            'status' => ['required', Rule::in(['Pending', 'Approved', 'Completed'])],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:5120'],
        ]);

        // Handle optional attachment replacement
        if ($request->hasFile('attachment')) {
            // Delete old attachment if exists
            if ($purchaseOrder->attachment_path && Storage::disk('public')->exists($purchaseOrder->attachment_path)) {
                Storage::disk('public')->delete($purchaseOrder->attachment_path);
            }
            $validated['attachment_path'] = $request->file('attachment')->store('purchase_orders', 'public');
        }

        $purchaseOrder->update($validated);

        return redirect()
            ->route('purchase-orders.index')
            ->with('success', 'Purchase order updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        // Only allow deletion of pending orders
        if ($purchaseOrder->status !== 'Pending') {
            return redirect()
                ->route('purchase-orders.index')
                ->with('error', 'Only pending purchase orders can be deleted.');
        }

        // Delete attachment if exists
        if ($purchaseOrder->attachment_path && Storage::disk('public')->exists($purchaseOrder->attachment_path)) {
            Storage::disk('public')->delete($purchaseOrder->attachment_path);
        }

        $purchaseOrder->delete();

        return redirect()
            ->route('purchase-orders.index')
            ->with('success', 'Purchase order deleted successfully.');
    }
}
