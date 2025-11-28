<?php

namespace Database\Seeders;

use App\Models\DeliveryOrder;
use App\Models\PurchaseOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DeliveryOrderSeeder extends Seeder
{
    public function run(): void
    {
        try {
            if (DB::getSchemaBuilder()->hasTable('delivery_orders') && DB::getSchemaBuilder()->hasTable('purchase_orders')) {
                // Get all existing purchase orders
                $purchaseOrders = PurchaseOrder::all();

                if ($purchaseOrders->isEmpty()) {
                    return; // Skip if no purchase orders exist
                }

                $deliveryOrdersData = [
                    [
                        'purchase_order_id' => $purchaseOrders[0]->id,
                        'do_number' => 'DO-2025-0001',
                        'delivery_date' => now()->addDays(5)->toDateString(),
                        'is_received' => false,
                        'notes' => 'First shipment of office stationery',
                        'file_path' => null,
                    ],
                    [
                        'purchase_order_id' => $purchaseOrders[0]->id,
                        'do_number' => 'DO-2025-0002',
                        'delivery_date' => now()->addDays(3)->toDateString(),
                        'is_received' => true,
                        'notes' => 'Partial delivery received',
                        'file_path' => null,
                    ],
                ];

                if ($purchaseOrders->count() > 1) {
                    $deliveryOrdersData[] = [
                        'purchase_order_id' => $purchaseOrders[1]->id,
                        'do_number' => 'DO-2025-0003',
                        'delivery_date' => now()->addDays(7)->toDateString(),
                        'is_received' => false,
                        'notes' => 'IT peripherals pending delivery',
                        'file_path' => null,
                    ];
                }

                if ($purchaseOrders->count() > 2) {
                    $deliveryOrdersData[] = [
                        'purchase_order_id' => $purchaseOrders[2]->id,
                        'do_number' => 'DO-2025-0004',
                        'delivery_date' => now()->subDays(2)->toDateString(),
                        'is_received' => true,
                        'notes' => 'Printer toner cartridges delivered',
                        'file_path' => null,
                    ];
                }

                foreach ($deliveryOrdersData as $data) {
                    DeliveryOrder::updateOrCreate(
                        ['do_number' => $data['do_number']],
                        $data
                    );
                }
            }
        } catch (\Throwable $e) {
            // Ignore if tables not migrated yet
        }
    }
}
