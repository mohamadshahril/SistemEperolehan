<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\PurchaseRequest;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure we have some users
        $users = User::factory(5)->create();

        // Option A: Create requests attached to random existing users
        PurchaseRequest::factory(25)
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
