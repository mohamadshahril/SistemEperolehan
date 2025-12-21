<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Default admin user
        $admin = User::updateOrCreate(
            ['email' => 'admin@example.com'],
            [
                'name' => 'Admin User',
                'password' => 'password', // hashed by User model casts
                'ic_no' => '800101016666',
                'staff_id' => 'A0001',
                'location_iso_code' => 'MY-01',
                'email_verified_at' => now(),
            ]
        );

        // Sample users (idempotent enough; factory creates different emails)
        if (User::count() < 6) {
            User::factory(5)->create();
        }
    }
}
