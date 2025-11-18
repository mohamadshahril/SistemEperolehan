<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Status;

class StatusesSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['name' => 'Pending', 'description' => 'Waiting for approval'],
            ['name' => 'Approved', 'description' => 'Request has been approved'],
            ['name' => 'Rejected', 'description' => 'Request has been rejected'],
        ];

        foreach ($rows as $row) {
            Status::query()->updateOrCreate(
                ['name' => $row['name']],
                ['description' => $row['description']]
            );
        }
    }
}
