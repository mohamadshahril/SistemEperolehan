<?php

namespace App\Http\Controllers;

use App\Models\DeliveryOrder;
use App\Models\Vendor;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DeliveryReportController extends Controller
{
    public function index(Request $request)
    {
        $filters = [
            'from_date' => $request->query('from_date'),
            'to_date' => $request->query('to_date'),
            'vendor_id' => $request->query('vendor_id'),
            'status' => $request->query('status'),
            'report_type' => $request->query('report_type', 'list'), // 'list', 'monthly', 'quarterly', 'yearly'
            'selected_period' => $request->query('selected_period'),
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

        // Apply selected period filter if coming from report drill-down
        if ($filters['selected_period']) {
            $period = $filters['selected_period'];
            if ($filters['report_type'] === 'monthly') {
                $query->whereRaw('DATE_FORMAT(delivery_date, "%Y-%m") = ?', [$period]);
            } elseif ($filters['report_type'] === 'quarterly') {
                if (preg_match('/^(\d{4})-Q(\d)$/', $period, $matches)) {
                    $query->whereYear('delivery_date', $matches[1])
                          ->whereRaw('QUARTER(delivery_date) = ?', [$matches[2]]);
                }
            } elseif ($filters['report_type'] === 'yearly') {
                $query->whereYear('delivery_date', $period);
            }
        }

        // Sorting (only for list view)
        if (in_array($filters['sort_by'], ['delivery_date', 'do_number', 'is_received'])) {
            $query->orderBy($filters['sort_by'], $filters['sort_dir']);
        } else {
            $query->orderBy('delivery_date', 'desc');
        }

        // Get period data if needed
        $periodData = [];
        if ($filters['report_type'] !== 'list') {
            $periodData = $this->getPeriodData($filters);
        }

        $deliveryOrders = $query->paginate(25);

        // Calculate stats based on filtered query (excluding pagination and selected_period for overall stats,
        // but wait, if a period is selected, stats should reflect that period)
        $statsQuery = DeliveryOrder::whereHas('purchaseOrder', function ($q) {
                $q->whereNull('deleted_at');
            });

        if ($filters['from_date']) $statsQuery->whereDate('delivery_date', '>=', $filters['from_date']);
        if ($filters['to_date']) $statsQuery->whereDate('delivery_date', '<=', $filters['to_date']);
        if ($filters['vendor_id']) {
            $statsQuery->whereHas('purchaseOrder', function ($q) use ($filters) {
                $q->where('vendor_id', $filters['vendor_id']);
            });
        }
        if ($filters['status'] !== null && $filters['status'] !== '') {
            $filters['status'] === 'received'
                ? $statsQuery->where('is_received', true)
                : $statsQuery->where('is_received', false);
        }
        if ($filters['selected_period']) {
            $period = $filters['selected_period'];
            if ($filters['report_type'] === 'monthly') {
                $statsQuery->whereRaw('DATE_FORMAT(delivery_date, "%Y-%m") = ?', [$period]);
            } elseif ($filters['report_type'] === 'quarterly') {
                if (preg_match('/^(\d{4})-Q(\d)$/', $period, $matches)) {
                    $statsQuery->whereYear('delivery_date', $matches[1])
                               ->whereRaw('QUARTER(delivery_date) = ?', [$matches[2]]);
                }
            } elseif ($filters['report_type'] === 'yearly') {
                $statsQuery->whereYear('delivery_date', $period);
            }
        }

        $totalCount = $statsQuery->count();
        $receivedCount = $statsQuery->clone()->where('is_received', true)->count();

        $stats = [
            'total' => $totalCount,
            'received' => $receivedCount,
            'pending' => $totalCount - $receivedCount,
            'received_percentage' => $totalCount > 0
                ? round(($receivedCount / $totalCount) * 100, 1)
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
            'periodData' => $periodData,
            'vendors' => $vendors,
        ]);
    }

    private function getPeriodData($filters)
    {
        $query = DeliveryOrder::with('purchaseOrder.vendor')
            ->whereHas('purchaseOrder', function ($q) {
                $q->whereNull('deleted_at');
            });

        // Apply same filters
        if ($filters['from_date']) {
            $query->whereDate('delivery_date', '>=', $filters['from_date']);
        }
        if ($filters['to_date']) {
            $query->whereDate('delivery_date', '<=', $filters['to_date']);
        }
        if ($filters['vendor_id']) {
            $query->whereHas('purchaseOrder', function ($q) use ($filters) {
                $q->where('vendor_id', $filters['vendor_id']);
            });
        }
        if ($filters['status'] !== null && $filters['status'] !== '') {
            $filters['status'] === 'received'
                ? $query->where('is_received', true)
                : $query->where('is_received', false);
        }

        $results = [];
        $reportType = $filters['report_type'];

        if ($reportType === 'monthly') {
            $data = $query->selectRaw('DATE_FORMAT(delivery_date, "%Y-%m") as period, COUNT(*) as total, SUM(CASE WHEN is_received = 1 THEN 1 ELSE 0 END) as received')
                ->groupBy('period')
                ->orderBy('period', 'desc')
                ->get();

            foreach ($data as $item) {
                $results[] = [
                    'period' => $item->period,
                    'label' => Carbon::createFromFormat('Y-m', $item->period)->format('M Y'),
                    'total' => $item->total,
                    'received' => $item->received,
                    'pending' => $item->total - $item->received,
                    'percentage' => $item->total > 0 ? round(($item->received / $item->total) * 100, 1) : 0,
                ];
            }
        } elseif ($reportType === 'quarterly') {
            $data = $query->selectRaw('YEAR(delivery_date) as year, QUARTER(delivery_date) as quarter, COUNT(*) as total, SUM(CASE WHEN is_received = 1 THEN 1 ELSE 0 END) as received')
                ->groupBy('year', 'quarter')
                ->orderBy('year', 'desc')
                ->orderBy('quarter', 'desc')
                ->get();

            foreach ($data as $item) {
                $results[] = [
                    'period' => $item->year . '-Q' . $item->quarter,
                    'label' => 'Q' . $item->quarter . ' ' . $item->year,
                    'total' => $item->total,
                    'received' => $item->received,
                    'pending' => $item->total - $item->received,
                    'percentage' => $item->total > 0 ? round(($item->received / $item->total) * 100, 1) : 0,
                ];
            }
        } elseif ($reportType === 'yearly') {
            $data = $query->selectRaw('YEAR(delivery_date) as year, COUNT(*) as total, SUM(CASE WHEN is_received = 1 THEN 1 ELSE 0 END) as received')
                ->groupBy('year')
                ->orderBy('year', 'desc')
                ->get();

            foreach ($data as $item) {
                $results[] = [
                    'period' => (string)$item->year,
                    'label' => (string)$item->year,
                    'total' => $item->total,
                    'received' => $item->received,
                    'pending' => $item->total - $item->received,
                    'percentage' => $item->total > 0 ? round(($item->received / $item->total) * 100, 1) : 0,
                ];
            }
        }

        return $results;
    }

    public function exportPdf(Request $request)
    {
        $filters = [
            'from_date' => $request->query('from_date'),
            'to_date' => $request->query('to_date'),
            'vendor_id' => $request->query('vendor_id'),
            'status' => $request->query('status'),
            'report_type' => $request->query('report_type', 'list'),
            'selected_period' => $request->query('selected_period'),
            'sort_by' => $request->query('sort_by', 'delivery_date'),
            'sort_dir' => $request->query('sort_dir', 'desc'),
        ];

        $query = DeliveryOrder::with('purchaseOrder.vendor')
            ->whereHas('purchaseOrder', function ($q) {
                $q->whereNull('deleted_at');
            });

        // Apply filters
        if ($filters['from_date']) {
            $query->whereDate('delivery_date', '>=', $filters['from_date']);
        }
        if ($filters['to_date']) {
            $query->whereDate('delivery_date', '<=', $filters['to_date']);
        }
        if ($filters['vendor_id']) {
            $query->whereHas('purchaseOrder', function ($q) use ($filters) {
                $q->where('vendor_id', $filters['vendor_id']);
            });
        }
        if ($filters['status'] !== null && $filters['status'] !== '') {
            $filters['status'] === 'received'
                ? $query->where('is_received', true)
                : $query->where('is_received', false);
        }

        // Apply period filter if selected
        if ($filters['report_type'] !== 'list' && $filters['selected_period']) {
            $period = $filters['selected_period'];

            if ($filters['report_type'] === 'monthly') {
                // Filter by YYYY-MM format
                $query->whereRaw('DATE_FORMAT(delivery_date, "%Y-%m") = ?', [$period]);
            } elseif ($filters['report_type'] === 'quarterly') {
                // Filter by quarter (e.g., "2025-Q4")
                list($year, $quarter) = explode('-Q', $period);
                $query->whereRaw('YEAR(delivery_date) = ? AND QUARTER(delivery_date) = ?', [$year, $quarter]);
            } elseif ($filters['report_type'] === 'yearly') {
                // Filter by year
                $query->whereRaw('YEAR(delivery_date) = ?', [$period]);
            }
        }

        // Apply sorting
        if (in_array($filters['sort_by'], ['delivery_date', 'do_number', 'is_received'])) {
            $query->orderBy($filters['sort_by'], $filters['sort_dir']);
        } else {
            $query->orderBy('delivery_date', 'desc');
        }

        $deliveryOrders = $query->get();

        // Calculate stats
        $total = $deliveryOrders->count();
        $received = $deliveryOrders->where('is_received', true)->count();
        $pending = $deliveryOrders->where('is_received', false)->count();
        $receivedPercentage = $total > 0 ? round(($received / $total) * 100, 1) : 0;

        return view('reports.delivery-report-pdf', [
            'deliveryOrders' => $deliveryOrders,
            'filters' => $filters,
            'stats' => [
                'total' => $total,
                'received' => $received,
                'pending' => $pending,
                'received_percentage' => $receivedPercentage,
            ],
            'generatedAt' => Carbon::now()->format('d/m/Y H:i:s'),
        ]);
    }
}
