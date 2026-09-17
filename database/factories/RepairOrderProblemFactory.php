<?php

namespace Database\Factories;

use App\Models\Problem;
use App\Models\RepairOrder;
use App\Models\RepairOrderProblem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RepairOrderProblem>
 */
class RepairOrderProblemFactory extends Factory
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
            'problem_id' => Problem::factory(),
            'cost' => fake()->randomFloat(2, 150, 2500),
            'notes' => fake()->optional()->sentence(),
        ];
    }
}
