<?php

namespace Database\Factories;

use App\Models\RepairOrder;
use App\Models\RepairOrderPart;
use App\Models\SparePart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RepairOrderPart>
 */
class RepairOrderPartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'repair_order_id' => RepairOrder::factory(),
            'spare_part_id' => SparePart::factory(),
            'quantity' => fake()->numberBetween(1, 3),
            'unit_price' => fake()->randomFloat(2, 50, 1500),
        ];
    }
}
