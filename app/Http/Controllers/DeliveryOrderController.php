<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Http\Requests\DeliveryOrderStoreRequest;
use Illuminate\Support\Facades\Storage;

class DeliveryOrderController extends Controller
{
    // You would typically use middleware here for authorization

    public function index(Request $request)
    {
        $filters = [
            'from_date' => $request->query('from_date'),
            'to_date' => $request->query('to_date'),
            'vendor_id' => $request->query('vendor_id'),
            'status' => $request->query('status'),
            'search' => $request->query('search'),
            'sort_by' => $request->query('sort_by', 'delivery_date'),
            'sort_dir' => $request->query('sort_dir', 'desc'),
        ];

        $query = DeliveryOrder::with('purchaseOrder.vendor')
            ->whereHas('purchaseOrder', function ($q) {
                $q->whereNull('deleted_at');
            });

        // Apply search filter (DO number or vendor name)
        if ($filters['search']) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('do_number', 'like', "%{$search}%")
                  ->orWhereHas('purchaseOrder.vendor', function ($subQ) use ($search) {
                      $subQ->where('name', 'like', "%{$search}%");
                  });
            });
        }

        // Apply date range filter
        if ($filters['from_date']) {
            $query->whereDate('delivery_date', '>=', $filters['from_date']);
        }

        if ($filters['to_date']) {
            $query->whereDate('delivery_date', '<=', $filters['to_date']);
        }

        // Apply vendor filter
        if ($filters['vendor_id']) {
            $query->whereHas('purchaseOrder', function ($q) use ($filters) {
                $q->where('vendor_id', $filters['vendor_id']);
            });
        }

        // Apply status filter
        if ($filters['status'] !== null && $filters['status'] !== '') {
            $filters['status'] === 'received'
                ? $query->where('is_received', true)
                : $query->where('is_received', false);
        }

        // Sorting
        if (in_array($filters['sort_by'], ['delivery_date', 'do_number', 'is_received'])) {
            $query->orderBy($filters['sort_by'], $filters['sort_dir']);
        } else {
            $query->orderBy('delivery_date', 'desc');
        }

        $deliveryOrders = $query->paginate(10);

        // Get vendors for dropdown
        $vendors = Vendor::select('id', 'name')
            ->where('deleted_at', null)
            ->orderBy('name')
            ->get();

        return Inertia::render('delivery-orders/Index', [
            'deliveryOrders' => $deliveryOrders,
            'filters' => $filters,
            'vendors' => $vendors,
        ]);
    }

    public function create()
    {
        // Fetch purchase orders that haven't been fully delivered yet
        $purchaseOrders = PurchaseOrder::select('id', 'order_number', 'created_at')->get();

        return Inertia::render('delivery-orders/Create', [
            'purchaseOrders' => $purchaseOrders,
        ]);
    }

    public function store(DeliveryOrderStoreRequest $request)
    {
        $validated = $request->validated();
        $filePath = null;

        // 1. Handle File Upload
        if ($request->hasFile('delivery_file')) {
            // Store file in the 'delivery_orders' disk (e.g., storage/app/public/delivery_orders)
            $filePath = $request->file('delivery_file')->store('delivery_orders', 'public');
        }

        // 2. Create the Delivery Order record
        DeliveryOrder::create(array_merge($validated, [
            'file_path' => $filePath,
        ]));

        return redirect()->route('delivery-orders.index')
            ->with('success', 'Delivery Order uploaded and recorded successfully.');
    }

    /**
     * Custom Action: Confirm Delivery (Mark as received).
     */
    public function confirm(DeliveryOrder $deliveryOrder)
    {
        // You should add a policy check here (e.g., $this->authorize('confirm', $deliveryOrder);)

        $deliveryOrder->update([
            'is_received' => true,
        ]);

        return back()->with('success', 'Delivery for DO number ' . $deliveryOrder->do_number . ' confirmed.');
    }

    /**
     * Custom Action: Print Delivery Summary.
     */
    public function printSummary(DeliveryOrder $deliveryOrder)
    {
        // For a simple report, you can pass data to an Inertia page for printing
        return Inertia::render('delivery-orders/PrintSummary', [
            'deliveryOrder' => $deliveryOrder->load('purchaseOrder.vendor'),
        ]);

        // Alternatively, use a PDF generation library like Dompdf or Snappy for a server-side generated PDF.
    }

    // Edit form
    public function edit(DeliveryOrder $deliveryOrder)
    {
        $purchaseOrders = PurchaseOrder::select('id', 'order_number')->get();

        return Inertia::render('delivery-orders/Edit', [
            'deliveryOrder' => $deliveryOrder->load('purchaseOrder.vendor'),
            'purchaseOrders' => $purchaseOrders,
        ]);
    }

    // Update action
    public function update(Request $request, DeliveryOrder $deliveryOrder)
    {
        $validated = $request->validate([
            'purchase_order_id' => ['required', 'exists:purchase_orders,id'],
            'do_number' => ['required', 'string', 'max:255', 'unique:delivery_orders,do_number,' . $deliveryOrder->id],
            'delivery_date' => ['required', 'date'],
            'delivery_file' => ['nullable', 'file', 'mimes:pdf,jpg,png', 'max:5120'],
            'notes' => ['nullable', 'string'],
        ]);

        // Handle file replacement
        if ($request->hasFile('delivery_file')) {
            // delete old file if exists
            if ($deliveryOrder->file_path) {
                try {
                    Storage::disk('public')->delete($deliveryOrder->file_path);
                } catch (\Throwable $e) {
                    // ignore deletion errors
                }
            }

            $filePath = $request->file('delivery_file')->store('delivery_orders', 'public');
            $validated['file_path'] = $filePath;
        }

        $deliveryOrder->update($validated);

        return redirect()->route('delivery-orders.index')
            ->with('success', 'Delivery Order updated successfully.');
    }

    // Delete action
    public function destroy(DeliveryOrder $deliveryOrder)
    {
        // Delete attached file if exists
        if ($deliveryOrder->file_path) {
            try {
                Storage::disk('public')->delete($deliveryOrder->file_path);
            } catch (\Throwable $e) {
                // ignore deletion errors
            }
        }

        $doNumber = $deliveryOrder->do_number;
        $deliveryOrder->delete();

        return redirect()->route('delivery-orders.index')
            ->with('success', 'Delivery Order ' . $doNumber . ' deleted successfully.');
    }
}