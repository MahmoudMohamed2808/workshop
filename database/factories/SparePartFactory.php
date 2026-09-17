<?php

namespace Database\Factories;

use App\Models\SparePart;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<SparePart>
 */
class SparePartFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->bothify('PRT-####-???'),
            'name' => fake()->unique()->words(2, true),
            'purchase_price' => fake()->randomFloat(2, 10, 500),
            'selling_price' => fake()->randomFloat(2, 20, 800),
            'quantity' => fake()->numberBetween(0, 100),
            'minimum_stock' => 5,
        ];
    }
}
