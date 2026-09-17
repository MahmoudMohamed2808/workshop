<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\RepairOrder;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Invoice>
 */
class InvoiceFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_number' => fake()->unique()->bothify('INV-2026-######'),
            'repair_order_id' => RepairOrder::factory(),
            'subtotal' => 0,
            'discount' => 0,
            'total' => 0,
            'paid_total' => 0,
            'remaining_total' => 0,
            'issued_at' => now(),
        ];
    }
}
