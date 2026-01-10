<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Default users
        $users = [
            [
                'email' => 'admin@example.com',
                'name' => 'Admin User',
                'password' => 'password',
                'ic_no' => '800101016666',
                'staff_id' => 'A0001',
                'location_iso_code' => 'MY-01',
            ],
            [
                'email' => 'manager@example.com',
                'name' => 'Manager User',
                'password' => 'password',
                'ic_no' => '800101017777',
                'staff_id' => 'M0001',
                'location_iso_code' => 'MY-01',
            ],
            [
                'email' => 'staff@example.com',
                'name' => 'Staff User',
                'password' => 'password',
                'ic_no' => '800101018888',
                'staff_id' => 'S0001',
                'location_iso_code' => 'MY-01',
            ],
            [
                'email' => 'procurement@example.com',
                'name' => 'Procurement Officer',
                'password' => 'password',
                'ic_no' => '800101019999',
                'staff_id' => 'P0001',
                'location_iso_code' => 'MY-01',
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, ['email_verified_at' => now()])
            );
        }

        // Sample users (idempotent enough; factory creates different emails)
        if (User::count() < 10) {
            User::factory(5)->create();
        }
    }
}
