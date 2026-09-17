<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<InvoiceItem>
 */
class InvoiceItemFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $quantity = fake()->numberBetween(1, 2);
        $unitPrice = fake()->randomFloat(2, 100, 1000);

        return [
            'invoice_id' => Invoice::factory(),
            'description' => fake()->sentence(3),
            'type' => fake()->randomElement(['part', 'labor', 'problem', 'additional', 'other']),
            'quantity' => $quantity,
            'unit_price' => $unitPrice,
            'total' => $quantity * $unitPrice,
        ];
    }
}
