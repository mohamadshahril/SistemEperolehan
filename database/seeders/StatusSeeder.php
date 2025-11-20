<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StatusSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();
        $rows = [
            ['name' => 'Pending', 'description' => 'Awaiting approval', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Approved', 'description' => 'Approved by manager', 'created_at' => $now, 'updated_at' => $now],
            ['name' => 'Rejected', 'description' => 'Rejected by manager', 'created_at' => $now, 'updated_at' => $now],
        ];

        // Idempotent upsert by unique key on name
        DB::table('statuses')->upsert($rows, ['name'], ['description', 'updated_at']);
    }
}
