<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\DeviceModel;
use App\Models\DeviceType;
use App\Models\Problem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Problem>
 */
class ProblemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'device_type_id' => DeviceType::factory(),
            'brand_id' => Brand::factory(),
            'device_model_id' => DeviceModel::factory(),
            'name' => fake()->unique()->words(3, true),
            'description' => fake()->optional()->sentence(),
            'default_cost_min' => fake()->optional()->randomFloat(2, 50, 500),
            'default_cost_max' => fake()->optional()->randomFloat(2, 500, 2000),
            'usage_count' => 0,
            'is_active' => true,
        ];
    }
}
