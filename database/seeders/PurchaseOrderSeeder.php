<?php

namespace Database\Seeders;

use App\Models\PurchaseOrder;
use App\Models\Vendor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class PurchaseOrderSeeder extends Seeder
{
    public function run(): void
    {
        try {
            if (DB::getSchemaBuilder()->hasTable('purchase_orders') && DB::getSchemaBuilder()->hasTable('vendors')) {
                $vendors = Vendor::all();

                if ($vendors->isEmpty()) {
                    // Create some default vendors if none exist
                    $vendorsData = [
                        ['name' => 'Acme Supplies Sdn Bhd', 'email' => 'sales@acme.example', 'phone' => '+60-3-1111-2222'],
                        ['name' => 'Borneo Tech Enterprise', 'email' => 'hello@borneo-tech.example', 'phone' => '+60-82-123456'],
                        ['name' => 'Metro Office Solutions', 'email' => 'contact@metro-office.example', 'phone' => '+60-3-2222-3333'],
                    ];

                    foreach ($vendorsData as $data) {
                        $vendors[] = Vendor::updateOrCreate(['name' => $data['name']], $data);
                    }
                }

                $total = 100;
                $years = [2026, 2025, 2024];
                $perYearBase = intdiv($total, count($years));
                $remainder = $total % count($years);

                $seqPerYear = array_fill_keys($years, 0);
                $vendorCount = $vendors->count();

                foreach ($years as $i => $year) {
                    $countThisYear = $perYearBase + ($i < $remainder ? 1 : 0);
                    for ($j = 0; $j < $countThisYear; $j++) {
                        $seqPerYear[$year]++;
                        $seq = str_pad((string) $seqPerYear[$year], 4, '0', STR_PAD_LEFT);
                        $orderNumber = "PO-{$year}-{$seq}";

                        $vendor = $vendors[$j % $vendorCount];

                        $details = match ($j % 5) {
                            0 => 'Office supplies for HQ',
                            1 => 'IT equipment upgrade',
                            2 => 'Furniture for new branch',
                            3 => 'Maintenance tools and spare parts',
                            default => 'General procurement',
                        };

                        PurchaseOrder::updateOrCreate(
                            ['order_number' => $orderNumber],
                            [
                                'vendor_id' => $vendor->id,
                                'details' => $details,
                                'created_at' => now()->setDate($year, 1, 1)->addDays(rand(0, 364)),
                            ]
                        );
                    }
                }
            }
        } catch (\Throwable $e) {
            // Ignore if tables not migrated yet
        }
    }
}
