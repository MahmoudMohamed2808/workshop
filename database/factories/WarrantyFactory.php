<?php

namespace Database\Factories;

use App\Models\RepairOrder;
use App\Models\Warranty;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Warranty>
 */
class WarrantyFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $duration = fake()->randomElement([30, 60, 90]);
        $startsAt = fake()->dateTimeBetween('-1 month', 'now');

        return [
            'repair_order_id' => RepairOrder::factory(),
            'duration_days' => $duration,
            'starts_at' => $startsAt->format('Y-m-d'),
            'ends_at' => (clone $startsAt)->modify('+'.$duration.' days')->format('Y-m-d'),
        ];
    }
}
