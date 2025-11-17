<?php

namespace Database\Seeders;

use App\Models\Vot;
use Illuminate\Database\Seeder;

class VotsSeeder extends Seeder
{
    public function run(): void
    {
        // Canonical VOT list (sample). Adjust as needed for your organization.
        $vots = [
            ['vot_code' => 10000, 'vot_name' => 'Emoluments', 'status' => 1],
            ['vot_code' => 20000, 'vot_name' => 'Services and Supplies', 'status' => 1],
            ['vot_code' => 30000, 'vot_name' => 'Assets', 'status' => 1],
            ['vot_code' => 40000, 'vot_name' => 'Grants and Fixed Charges', 'status' => 1],
            ['vot_code' => 50000, 'vot_name' => 'Other Expenditures', 'status' => 2],
        ];

        foreach ($vots as $row) {
            // Be idempotent and handle soft-deleted rows gracefully.
            $existing = Vot::withTrashed()->where('vot_code', $row['vot_code'])->first();

            if ($existing) {
                // Update attributes and restore if soft-deleted
                $existing->fill([
                    'vot_name' => $row['vot_name'],
                    'status' => $row['status'],
                ]);
                if ($existing->trashed()) {
                    $existing->restore();
                }
                $existing->save();
            } else {
                Vot::create($row);
            }
        }
    }
}
