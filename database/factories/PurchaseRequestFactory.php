<?php

namespace Database\Factories;

use App\Models\PurchaseRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<\App\Models\PurchaseRequest>
 */
class PurchaseRequestFactory extends Factory
{
    protected $model = PurchaseRequest::class;

    public function definition(): array
    {
        // Randomize status; adjust submitted_at accordingly
        $status = $this->faker->randomElement(['Pending', 'Approved', 'Rejected']);

        // If submitted, pick a recent timestamp; for pending, sometimes null
        $submittedAt = $status === 'Pending'
            ? $this->faker->optional(0.6)->dateTimeBetween('-10 days', 'now')
            : $this->faker->dateTimeBetween('-30 days', 'now');

        return [
            'user_id' => User::factory(), // or set explicitly in seeder if you prefer existing users
            'item_name' => $this->faker->words(nb: 3, asText: true),
            'quantity' => $this->faker->numberBetween(1, 100),
            'price' => $this->faker->randomFloat(2, 10, 5000), // matches decimal(12,2)
            'purpose' => $this->faker->optional()->sentence(12),
            'status' => $status,
            'submitted_at' => $submittedAt,
            'attachment_path' => $this->faker->optional(0.3)->filePath(),
            // do NOT set deleted_at; soft deletes added via migration will default to NULL
        ];
    }
}
