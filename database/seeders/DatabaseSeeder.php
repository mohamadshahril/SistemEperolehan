<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\PurchaseRequest;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1) Users (needed by purchase requests and user_locations)
        $users = User::factory(5)->create();

        // 2) Reference data
        $this->call([
            LocationsSeeder::class,
            VendorsSeeder::class,
        ]);

        // 3) Business data depending on reference data
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
