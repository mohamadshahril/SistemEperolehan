<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\DB;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\User>
 */
class UserFactory extends Factory
{
    /**
     * The current password being used by the factory.
     */
    protected static ?string $password;

    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        // Try to pick a valid location ISO code if locations have been seeded; otherwise keep it null
        $locationIso = null;
        try {
            if (DB::getSchemaBuilder()->hasTable('locations')) {
                $iso = DB::table('locations')->inRandomOrder()->value('location_iso_code');
                $locationIso = $iso ?: null;
            }
        } catch (\Throwable $e) {
            // Ignore DB errors during factory bootstrapping (e.g., before migrations run)
            $locationIso = null;
        }

        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'email_verified_at' => now(),
            'password' => static::$password ??= 'password',
            'remember_token' => Str::random(10),
            'two_factor_secret' => Str::random(10),
            'two_factor_recovery_codes' => Str::random(10),
            'two_factor_confirmed_at' => now(),
            // Required by users table schema
            'ic_no' => $this->generateIcNo(),
            'staff_id' => $this->generateStaffId(),
            // Use a sensible default if none could be determined
            'location_iso_code' => $locationIso ?? 'MY-01',
        ];
    }

    /**
     * Indicate that the model's email address should be unverified.
     */
    public function unverified(): static
    {
        return $this->state(fn (array $attributes) => [
            'email_verified_at' => null,
        ]);
    }

    /**
     * Indicate that the model does not have two-factor authentication configured.
     */
    public function withoutTwoFactor(): static
    {
        return $this->state(fn (array $attributes) => [
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ]);
    }

    /**
     * Generate a realistic staff ID example (e.g., 03511, c23134, c23234_1).
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

    /**
     * Generate a Malaysian-style IC number (12 digits) or similar numeric string.
     */
    protected function generateIcNo(): string
    {
        return (string) fake()->numerify('############');
    }
}
