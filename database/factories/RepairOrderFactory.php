<?php

namespace Database\Factories;

use App\Models\Brand;
use App\Models\Customer;
use App\Models\DeviceModel;
use App\Models\DeviceType;
use App\Models\RepairOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<RepairOrder>
 */
class RepairOrderFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_number' => fake()->unique()->bothify('RO-2026-######'),
            'customer_id' => Customer::factory(),
            'device_type_id' => DeviceType::factory(),
            'brand_id' => Brand::factory(),
            'device_model_id' => DeviceModel::factory(),
            'serial_number' => fake()->optional()->bothify('SN########'),
            'device_condition' => fake()->optional()->sentence(),
            'accessories' => fake()->optional()->randomElements(['شاحن', 'ريموت', 'كرتونة', 'وصلة كهرباء'], fake()->numberBetween(0, 2)),
            'customer_complaint' => fake()->optional()->sentence(),
            'technician_diagnosis' => null,
            'technician_id' => User::factory(),
            'status' => 'received',
            'rejection_reason' => null,
            'estimated_completion_date' => fake()->optional()->date(),
            'labor_cost' => 0,
            'additional_cost' => 0,
            'discount' => 0,
            'total_cost' => 0,
            'approved_at' => null,
            'delivered_at' => null,
            'created_by' => User::factory(),
        ];
    }
}
