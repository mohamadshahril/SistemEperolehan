<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseRequest;

class PurchaseItemSeeder extends Seeder
{
    public function run(): void
    {
        try {
            $schema = DB::getSchemaBuilder();
            if (!$schema->hasTable('purchase_requests') || !$schema->hasTable('purchase_items')) {
                return; // migrations not ready
            }

            // For each purchase request that has no items, create 1–3 sample items
            PurchaseRequest::query()
                ->select(['id', 'budget', 'purchase_ref_no'])
                ->withCount('items')
                ->whereDoesntHave('items')
                ->chunkById(100, function ($prs) {
                    foreach ($prs as $pr) {
                        // Determine number of items (1 to 3)
                        $count = random_int(1, 3);

                        // Distribute budget across items (keep totals <= budget)
                        $budget = (float) ($pr->budget ?? 0);
                        $remaining = $budget > 0 ? $budget : 0;

                        for ($i = 1; $i <= $count; $i++) {
                            // Simple generated values
                            $quantity = random_int(1, 5);
                            // If we have a budget, keep unit price modest and within remaining
                            $maxUnit = $remaining > 0 ? max(1, (int) floor($remaining / max(1, $quantity))) : random_int(10, 200);
                            $unitPrice = $maxUnit > 0 ? (float) random_int(5, max(5, min($maxUnit, 500))) : (float) random_int(5, 200);

                            $totalPrice = $unitPrice * $quantity;
                            if ($remaining > 0 && $totalPrice > $remaining) {
                                // Cap to remaining budget if overflow
                                if ($quantity > 0) {
                                    $unitPrice = round($remaining / $quantity, 2);
                                }
                                $totalPrice = $unitPrice * $quantity;
                            }
                            $remaining = max(0, $remaining - $totalPrice);

                            $pr->items()->create([
                                'purchase_ref_no' => $pr->purchase_ref_no,
                                'item_name' => 'Sample Item ' . $i,
                                'item_code' => null,
                                'purpose' => 'Seeded data for testing',
                                'unit' => 'pcs',
                                'quantity' => $quantity,
                                'unit_price' => $unitPrice,
                                'total_price' => $totalPrice,
                            ]);
                        }
                    }
                });
        } catch (\Throwable $e) {
            // Silently skip if anything goes wrong during early development
        }
    }
}
