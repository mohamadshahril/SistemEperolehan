<?php

namespace Database\Seeders;

use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserLocationsSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure locations exist
        if (Location::count() === 0) {
            $this->call(LocationsSeeder::class);
        }

        $locations = Location::orderBy('id')->pluck('location_iso_code');
        if ($locations->isEmpty()) {
            return; // nothing to assign
        }

        $users = User::orderBy('id')->get();
        if ($users->isEmpty()) {
            return; // no users to map
        }

        $idx = 0;
        $totalLoc = $locations->count();

        foreach ($users as $user) {
            $iso = $locations[$idx % $totalLoc];
            $idx++;

            // Upsert by staff_id; using a synthetic staff code for demo data
            DB::table('user_locations')->updateOrInsert(
                ['staff_id' => sprintf('STF%04d', $user->id)],
                [
                    'location_iso_code' => $iso,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }
}
