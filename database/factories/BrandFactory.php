<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\DeviceType;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Brand>
 */
class BrandFactory extends Factory
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
            'name' => fake()->unique()->words(2, true),
            'is_active' => true,
        ];
    }
}
