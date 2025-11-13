<?php

namespace App\Http\Controllers;

use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

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

        $query = PurchaseOrder::with('vendor');

        // Search by order number or details
        if ($search = $request->string('search')->toString()) {
            $query->where(function ($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                    ->orWhere('details', 'like', "%{$search}%")
                    ->orWhereHas('vendor', function ($vendorQuery) use ($search) {
                        $vendorQuery->where('name', 'like', "%{$search}%");
                    });
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

        return response()->json($purchaseOrders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'vendor_id' => ['required', 'exists:vendors,id'],
            'details' => ['nullable', 'string', 'max:1000'],
            'status' => ['nullable', Rule::in(['Pending', 'Approved', 'Completed'])],
            'attachment' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png,doc,docx,xls,xlsx', 'max:5120'],
        ]);

        // Auto-generate unique order number
        $date = date('Ymd');
        $count = PurchaseOrder::withTrashed()
            ->whereDate('created_at', today())
            ->count();
        $orderNumber = sprintf('PO-%s-%04d', $date, $count + 1);

        // Handle file upload
        $attachmentPath = null;
        if ($request->hasFile('attachment')) {
            $attachmentPath = $request->file('attachment')->store('purchase_orders', 'public');
        }

        $purchaseOrder = PurchaseOrder::create([
            'vendor_id' => $validated['vendor_id'],
            'order_number' => $orderNumber,
            'details' => $validated['details'] ?? null,
            'status' => $validated['status'] ?? 'Pending',
            'attachment_path' => $attachmentPath,
        ]);

        return response()->json([
            'message' => 'Purchase order created successfully.',
            'purchase_order' => $purchaseOrder->load('vendor'),
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        return response()->json($purchaseOrder->load('vendor'));
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

        return response()->json([
            'message' => 'Purchase order updated successfully.',
            'purchase_order' => $purchaseOrder->load('vendor'),
        ]);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        // Only allow deletion of pending orders
        if ($purchaseOrder->status !== 'Pending') {
            return response()->json([
                'message' => 'Only pending purchase orders can be deleted.',
            ], 422);
        }

        // Delete attachment if exists
        if ($purchaseOrder->attachment_path && Storage::disk('public')->exists($purchaseOrder->attachment_path)) {
            Storage::disk('public')->delete($purchaseOrder->attachment_path);
        }

        $purchaseOrder->delete();

        return response()->json([
            'message' => 'Purchase order deleted successfully.',
        ]);
    }
}
