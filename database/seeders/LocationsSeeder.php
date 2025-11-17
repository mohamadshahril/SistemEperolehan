<?php

namespace Database\Seeders;

use App\Models\Location;
use Illuminate\Database\Seeder;

class LocationsSeeder extends Seeder
{
    public function run(): void
    {
        // A small hierarchical sample: STATE > DISTRICT > OFFICE
        $locations = [
            // States
            ['location_iso_code' => 'MY-KDH', 'location_name' => 'KEDAH', 'parent_iso_code' => 'MY'],
            ['location_iso_code' => 'MY-SGR', 'location_name' => 'SELANGOR', 'parent_iso_code' => 'MY'],
            ['location_iso_code' => 'MY-JHR', 'location_name' => 'JOHOR', 'parent_iso_code' => 'MY'],

            // Districts (example)
            ['location_iso_code' => 'MY-SGR-PJ', 'location_name' => 'PETALING JAYA', 'parent_iso_code' => 'MY-SGR'],
            ['location_iso_code' => 'MY-SGR-SHAH', 'location_name' => 'SHAH ALAM', 'parent_iso_code' => 'MY-SGR'],
            ['location_iso_code' => 'MY-KDH-ALOR', 'location_name' => 'ALOR SETAR', 'parent_iso_code' => 'MY-KDH'],

            // Offices (example)
            ['location_iso_code' => 'MY-SGR-PJ-HQ', 'location_name' => 'PJ HQ', 'parent_iso_code' => 'MY-SGR-PJ'],
            ['location_iso_code' => 'MY-SGR-SHAH-DEP1', 'location_name' => 'SHAH ALAM DEP 1', 'parent_iso_code' => 'MY-SGR-SHAH'],
            ['location_iso_code' => 'MY-KDH-ALOR-OPS', 'location_name' => 'ALOR SETAR OPS', 'parent_iso_code' => 'MY-KDH-ALOR'],
        ];

        foreach ($locations as $loc) {
            // upsert by unique keys to be idempotent
            Location::updateOrCreate(
                ['location_iso_code' => $loc['location_iso_code']],
                array_merge($loc, ['status' => 1])
            );
        }
    }
}
