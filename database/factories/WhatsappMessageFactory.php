<?php

namespace Database\Factories;

use App\Models\Customer;
use App\Models\RepairOrder;
use App\Models\WhatsappMessage;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<WhatsappMessage>
 */
class WhatsappMessageFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'customer_id' => Customer::factory(),
            'repair_order_id' => RepairOrder::factory(),
            'type' => fake()->randomElement(['intake', 'diagnosis', 'approval_request', 'ready', 'invoice', 'payment_reminder', 'other']),
            'message' => fake()->sentence(),
            'media_url' => null,
            'status' => 'pending',
            'error_message' => null,
            'sent_at' => null,
        ];
    }
}
