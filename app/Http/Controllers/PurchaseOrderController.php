<?php

namespace App\Http\Controllers;

use App\Http\Requests\PurchaseOrderStoreRequest;
use App\Http\Requests\PurchaseOrderUpdateRequest;
use App\Models\PurchaseOrder;
use App\Models\PurchaseOrderItem;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Barryvdh\DomPDF\Facade\Pdf;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->get('search');
        $pos = PurchaseOrder::query()
            ->when($search, fn($q)=>$q->where('po_number','like',"%$search%"))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('purchase-orders/Index', [
            'pos' => $pos,
            'filters' => ['search' => $search],
        ]);
    }

    public function create()
    {
        return Inertia::render('purchase-orders/Create', [
            'approvedRequests' => [
                ['id'=>1,'request_number'=>'REQ-1001'],
                ['id'=>2,'request_number'=>'REQ-1002']
            ]
        ]);
    }

    public function store(PurchaseOrderStoreRequest $request)
    {
        $po = PurchaseOrder::create([
            'po_number' => 'PO-' . time(),
            'approved_request_id' => $request->approved_request_id,
            'vendor_id' => $request->vendor_id,
            'total_price' => $request->total_price,
            'status' => 'Created',
        ]);

        foreach ($request->items as $item) {
            $po->items()->create($item);
        }

        return redirect()->route('purchase-orders.index')
            ->with('success','Purchase Order created successfully.');
    }

    public function edit(PurchaseOrder $purchaseOrder)
    {
        return Inertia::render('purchase-orders/Edit', [
            'po' => $purchaseOrder->load('items'),
        ]);
    }

    public function update(PurchaseOrderUpdateRequest $request, PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->update($request->validated());
        return redirect()->route('purchase-orders.index')
            ->with('success','Purchase Order updated.');
    }

    public function destroy(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->delete();
        return redirect()->route('purchase-orders.index')
            ->with('success','Purchase Order deleted.');
    }

    public function downloadPdf(PurchaseOrder $purchaseOrder)
    {
        $pdf = Pdf::loadView('pdf.purchase_order', ['po'=>$purchaseOrder->load('items')]);
        return $pdf->download("{$purchaseOrder->po_number}.pdf");
    }
}
