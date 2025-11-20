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

            // Ensure each user has a staff_id; if missing, generate one and persist
            $staff = $user->staff_id;
            if (empty($staff)) {
                $staff = $this->generateStaffId();
                // Persist generated staff_id and optionally sync location_iso_code
                $user->staff_id = $staff;
                if (empty($user->location_iso_code)) {
                    $user->location_iso_code = $iso;
                }
                $user->save();
            } else {
                // If user already has staff_id but no location, set it for consistency
                if (empty($user->location_iso_code)) {
                    $user->location_iso_code = $iso;
                    $user->save();
                }
            }

            // Upsert by staff_id using users.staff_id
            DB::table('user_locations')->updateOrInsert(
                ['staff_id' => $staff],
                [
                    'location_iso_code' => $iso,
                    'updated_at' => now(),
                    'created_at' => now(),
                ]
            );
        }
    }

    /**
     * Generate a realistic staff ID example.
     */
    protected function generateStaffId(): string
    {
        $pattern = fake()->randomElement(['num', 'cnum', 'cnum_suf']);
        switch ($pattern) {
            case 'num':
                return str_pad((string) fake()->numberBetween(0, 99999), 5, '0', STR_PAD_LEFT);
            case 'cnum':
                return 'c' . fake()->numberBetween(20000, 29999);
            case 'cnum_suf':
            default:
                return 'c' . fake()->numberBetween(20000, 29999) . '_' . fake()->numberBetween(1, 9);
        }
    }
}
