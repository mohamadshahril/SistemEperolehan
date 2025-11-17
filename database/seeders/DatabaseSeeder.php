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
        ]);

        // 2) Users (after locations to allow factory to set location_iso_code)
        $users = User::factory(5)->create();

        // 3) Business data depending on reference data and users
        $this->call([
            PurchaseOrdersSeeder::class, // depends on vendors
            UserLocationsSeeder::class,  // depends on users + locations
        ]);

        // 4) Example: purchase requests linked to the created users
        PurchaseRequest::factory(500)
            ->recycle($users) // ensures user_id comes from the created users
            ->create();

        // Option B: If you prefer to attach all to a known user, e.g. Test User
        // $testUser = User::factory()->create([
        //     'name' => 'Test User',
        //     'email' => 'test@example.com',
        // ]);
        // PurchaseRequest::factory(25)->for($testUser)->create();
    }
}
