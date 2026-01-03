<?php

namespace Database\Seeders;

use App\Models\DeliveryOrder;
use App\Models\PurchaseOrder;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class DeliveryOrderSeeder extends Seeder
{
    public function run(): void
    {
        try {
            if (DB::getSchemaBuilder()->hasTable('delivery_orders') && DB::getSchemaBuilder()->hasTable('purchase_orders')) {
                // Get all existing purchase orders
                $purchaseOrders = PurchaseOrder::query()->select('id')->get();

                if ($purchaseOrders->isEmpty()) {
                    return; // Skip if no purchase orders exist
                }

                // We will create 100 Delivery Orders distributed across the last 5 years (including current).
                $total = 100;
                $years = [];
                $currentYear = now()->year;
                for ($y = 0; $y < 5; $y++) {
                    $years[] = $currentYear - $y; // e.g., 2026, 2025, 2024, 2023, 2022
                }

                // Compute how many per year (rough distribution)
                $perYearBase = intdiv($total, count($years)); // 20
                $remainder = $total % count($years); // 0..4

                $seqPerYear = array_fill_keys($years, 0);

                $records = [];
                $poCount = $purchaseOrders->count();
                $poIndex = 0;

                foreach ($years as $i => $year) {
                    $countThisYear = $perYearBase + ($i < $remainder ? 1 : 0);
                    for ($j = 0; $j < $countThisYear; $j++) {
                        // Increment yearly sequence for DO number
                        $seqPerYear[$year]++;
                        $seq = str_pad((string) $seqPerYear[$year], 4, '0', STR_PAD_LEFT);
                        $doNumber = "DO-{$year}-{$seq}";

                        // Cycle through purchase orders to assign
                        $purchaseOrderId = $purchaseOrders[$poIndex % $poCount]->id;
                        $poIndex++;

                        // Spread delivery dates across the year (use day-of-year based on counters)
                        $dayOfYear = 1 + (($j * 17 + $i * 29) % 365); // pseudo spread
                        $deliveryDate = now()->setDate($year, 1, 1)->startOfDay()->addDays($dayOfYear - 1);

                        $isReceived = ($j % 3) !== 0; // approx 2/3 received
                        $notes = match (($j + $i) % 5) {
                            0 => 'Office supplies shipment',
                            1 => 'IT peripherals delivery',
                            2 => 'Furniture batch arrival',
                            3 => 'Maintenance tools consignment',
                            default => 'General goods delivery',
                        };

                        $records[] = [
                            'purchase_order_id' => $purchaseOrderId,
                            'do_number' => $doNumber,
                            'delivery_date' => $deliveryDate->toDateString(),
                            'is_received' => $isReceived,
                            'notes' => $notes,
                            'file_path' => null,
                        ];
                    }
                }

                foreach ($records as $data) {
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
