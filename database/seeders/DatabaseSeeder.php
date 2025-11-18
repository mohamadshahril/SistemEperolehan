<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\PurchaseRequest;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Reference data (locations first so users can reference location_iso_code)
        $this->call([
            LocationsSeeder::class,
            VotsSeeder::class,
            FileReferencesSeeder::class,
            TypeProcurementsSeeder::class,
            VendorsSeeder::class,
            // Ensure statuses exist before any factories/controllers rely on them
            StatusesSeeder::class,
        ]);

        // 2) Users (after locations to allow factory to set location_iso_code)
        $users = User::factory(5)->create();

        // 3) Business data depending on reference data and users
        $this->call([
            PurchaseOrdersSeeder::class, // depends on vendors
            UserLocationsSeeder::class,  // depends on users + locations
            PurchaseRequestsSeeder::class, // generate purchase requests with codes
        ]);

        // 4) Example alternative seeding strategies can be added here if needed
    }
}
