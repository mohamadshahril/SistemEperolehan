<?php

namespace Database\Seeders;

use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;
use Illuminate\Support\Collection;

class PurchaseOrdersSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure we have vendors first
        if (Vendor::count() === 0) {
            $this->call(VendorsSeeder::class);
        }

        $vendors = Vendor::orderBy('id')->get();
        if ($vendors->isEmpty()) {
            return; // nothing to seed without vendors
        }

        // We'll create a small deterministic set of orders, round-robin across vendors
        $datePart = now()->format('Ymd');
        $statuses = ['Pending', 'Approved', 'Completed'];

        $ordersToCreate = [];
        $counter = 1;
        foreach (range(1, 6) as $n) {
            /** @var Vendor $vendor */
            $vendor = $vendors[($n - 1) % $vendors->count()];
            $ordersToCreate[] = [
                'vendor_id' => $vendor->id,
                'order_number' => sprintf('PO-%s-%04d', $datePart, $counter++),
                'details' => 'Seeded purchase order for vendor #' . $vendor->id,
                'status' => $statuses[($n - 1) % count($statuses)],
            ];
        }

        foreach ($ordersToCreate as $data) {
            PurchaseOrder::updateOrCreate(
                ['order_number' => $data['order_number']],
                $data
            );
        }
    }
}
