<?php

namespace Database\Factories;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'invoice_id' => Invoice::factory(),
            'amount' => fake()->randomFloat(2, 100, 2000),
            'method' => fake()->randomElement(['cash', 'bank_transfer', 'wallet', 'card']),
            'paid_at' => now(),
            'notes' => fake()->optional()->sentence(),
            'received_by' => User::factory(),
        ];
    }
}
