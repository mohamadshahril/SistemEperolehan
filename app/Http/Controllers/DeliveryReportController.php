<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;

class DeliveryReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'from_date' => $request->query('from_date'),
            'to_date' => $request->query('to_date'),
            'vendor_id' => $request->query('vendor_id'),
            'status' => $request->query('status'),
            'sort_by' => $request->query('sort_by', 'delivery_date'),
            'sort_dir' => $request->query('sort_dir', 'desc'),
        ];

        $query = DeliveryOrder::with('purchaseOrder.vendor')
            ->whereHas('purchaseOrder', function ($q) {
                $q->whereNull('deleted_at');
            });

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

        $deliveryOrders = $query->paginate(25);

        // Calculate stats
        $stats = [
            'total' => DeliveryOrder::count(),
            'received' => DeliveryOrder::where('is_received', true)->count(),
            'pending' => DeliveryOrder::where('is_received', false)->count(),
            'received_percentage' => DeliveryOrder::count() > 0 
                ? round((DeliveryOrder::where('is_received', true)->count() / DeliveryOrder::count()) * 100, 1)
                : 0,
        ];

        // Get vendors for dropdown
        $vendors = Vendor::select('id', 'name')
            ->where('deleted_at', null)
            ->orderBy('name')
            ->get();

        return Inertia::render('delivery-reports/Index', [
            'deliveryOrders' => $deliveryOrders,
            'filters' => $filters,
            'stats' => $stats,
            'vendors' => $vendors,
        ]);
    }
}
