<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\PurchaseRequest;
use App\Models\User;

class PurchaseRequestSeeder extends Seeder
{
    public function run(): void
    {
        // Create one purchase request for every existing user (idempotent per user)
        try {
            $schema = DB::getSchemaBuilder();
            if ($schema->hasTable('users') && $schema->hasTable('purchase_requests')) {
                // Iterate all users and ensure each has at least one purchase request
                User::query()->chunkById(100, function ($users) {
                    foreach ($users as $user) {
                        // Skip if this user already has a purchase request (idempotent behavior)
                        $exists = PurchaseRequest::query()
                            ->where('user_id', $user->id)
                            ->exists();
                        if ($exists) {
                            continue;
                        }

                        // Create one PR for this user using factory defaults, overriding ownership/applicant
                        PurchaseRequest::factory(50)->create([
                            'user_id' => $user->id,
                            // applicant_id stores staff_id string
                            'applicant_id' => $user->staff_id,
                            // Prefer the user's location if available
                            'location_iso_code' => $user->location_iso_code ?? 'MY-01',
                        ]);
                    }
                });
            }
        } catch (\Throwable $e) {
            // Silently skip if migrations are not ready
        }
    }
}
