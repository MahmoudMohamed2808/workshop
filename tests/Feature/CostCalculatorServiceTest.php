<?php

use App\Models\Problem;
use App\Models\RepairOrder;
use App\Models\RepairOrderPart;
use App\Models\RepairOrderProblem;
use App\Models\SparePart;
use App\Services\CostCalculatorService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function pricedOrder(): RepairOrder
{
    return RepairOrder::factory()->create([
        'labor_cost' => 200,
        'additional_cost' => 50,
        'discount' => 100,
    ]);
}

test('sums problems parts labor and additional minus discount', function () {
    $order = pricedOrder();

    RepairOrderProblem::factory()->create([
        'repair_order_id' => $order->id,
        'problem_id' => Problem::factory()->create()->id,
        'cost' => 500,
    ]);

    $part = SparePart::factory()->create();

    RepairOrderPart::factory()->create([
        'repair_order_id' => $order->id,
        'spare_part_id' => $part->id,
        'quantity' => 2,
        'unit_price' => 150,
    ]);

    app(CostCalculatorService::class)->recalculateOrderTotal($order);

    // 500 (problems) + 300 (parts) + 200 (labor) + 50 (additional) - 100 (discount) = 950.
    expect((float) $order->fresh()->total_cost)->toBe(950.0);
});

test('recalculates when a problem cost is added', function () {
    $order = pricedOrder();

    app(CostCalculatorService::class)->recalculateOrderTotal($order);

    // 0 + 0 + 200 + 50 - 100 = 150.
    expect((float) $order->fresh()->total_cost)->toBe(150.0);

    RepairOrderProblem::factory()->create([
        'repair_order_id' => $order->id,
        'problem_id' => Problem::factory()->create()->id,
        'cost' => 500,
    ]);

    app(CostCalculatorService::class)->recalculateOrderTotal($order->fresh());

    expect((float) $order->fresh()->total_cost)->toBe(650.0);
});

test('recalculates when parts quantity and price change', function () {
    $order = pricedOrder();
    $part = SparePart::factory()->create();

    $orderPart = RepairOrderPart::factory()->create([
        'repair_order_id' => $order->id,
        'spare_part_id' => $part->id,
        'quantity' => 1,
        'unit_price' => 100,
    ]);

    app(CostCalculatorService::class)->recalculateOrderTotal($order);

    // 0 + 100 + 200 + 50 - 100 = 250.
    expect((float) $order->fresh()->total_cost)->toBe(250.0);

    $orderPart->update(['quantity' => 3, 'unit_price' => 150]);

    app(CostCalculatorService::class)->recalculateOrderTotal($order->fresh());

    // 0 + 450 + 200 + 50 - 100 = 600.
    expect((float) $order->fresh()->total_cost)->toBe(600.0);
});

test('recalculates when the discount changes', function () {
    $order = pricedOrder();

    RepairOrderProblem::factory()->create([
        'repair_order_id' => $order->id,
        'problem_id' => Problem::factory()->create()->id,
        'cost' => 500,
    ]);

    app(CostCalculatorService::class)->recalculateOrderTotal($order);

    expect((float) $order->fresh()->total_cost)->toBe(650.0);

    $order->update(['discount' => 250]);

    app(CostCalculatorService::class)->recalculateOrderTotal($order->fresh());

    // 500 + 0 + 200 + 50 - 250 = 500.
    expect((float) $order->fresh()->total_cost)->toBe(500.0);
});

test('never drops the total below zero', function () {
    $order = pricedOrder();

    $order->update(['discount' => 5000]);

    app(CostCalculatorService::class)->recalculateOrderTotal($order->fresh());

    expect((float) $order->fresh()->total_cost)->toBe(0.0);
});
