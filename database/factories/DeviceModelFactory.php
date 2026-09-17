<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\DeviceModel;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<DeviceModel>
 */
class DeviceModelFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'brand_id' => Brand::factory(),
            'name' => fake()->unique()->words(2, true),
        ];
    }
}
