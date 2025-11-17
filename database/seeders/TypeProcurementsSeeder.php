<?php

namespace Database\Seeders;

use App\Models\TypeProcurement;
use Illuminate\Database\Seeder;

class TypeProcurementsSeeder extends Seeder
{
    public function run(): void
    {
        // Canonical Type Procurement list (sample). Adjust as needed for your organization.
        $items = [
            ['procurement_code' => 10, 'procurement_description' => 'Direct Purchase', 'status' => 1],
            ['procurement_code' => 20, 'procurement_description' => 'Quotation', 'status' => 1],
            ['procurement_code' => 30, 'procurement_description' => 'Tender', 'status' => 1],
            ['procurement_code' => 40, 'procurement_description' => 'eBidding', 'status' => 2],
        ];

        foreach ($items as $row) {
            // Be idempotent and handle soft-deleted rows gracefully.
            $existing = TypeProcurement::withTrashed()
                ->where('procurement_code', $row['procurement_code'])
                ->first();

            if ($existing) {
                $existing->fill([
                    'procurement_description' => $row['procurement_description'],
                    'status' => $row['status'],
                ]);
                if ($existing->trashed()) {
                    $existing->restore();
                }
                $existing->save();
            } else {
                TypeProcurement::create($row);
            }
        }
    }
}
