<?php

use App\Models\RepairOrder;
use App\Models\RepairOrderPart;
use App\Models\SparePart;
use App\Services\InventoryService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('increases stock on restock with a reason', function () {
    $part = SparePart::factory()->create(['quantity' => 10]);

    app(InventoryService::class)->adjustStock($part, 5, 'توريد جديد');

    expect((int) $part->fresh()->quantity)->toBe(15);
});

test('decreases stock on write-off with a reason', function () {
    $part = SparePart::factory()->create(['quantity' => 10]);

    app(InventoryService::class)->adjustStock($part, -4, 'تالف');

    expect((int) $part->fresh()->quantity)->toBe(6);
});

test('refuses an adjustment without a reason', function () {
    $part = SparePart::factory()->create(['quantity' => 10]);

    app(InventoryService::class)->adjustStock($part, 5, '  ');
})->throws(InvalidArgumentException::class);

test('refuses a zero adjustment', function () {
    $part = SparePart::factory()->create(['quantity' => 10]);

    app(InventoryService::class)->adjustStock($part, 0, 'تسوية');
})->throws(InvalidArgumentException::class);

test('refuses to drop stock below zero', function () {
    $part = SparePart::factory()->create(['quantity' => 3]);

    app(InventoryService::class)->adjustStock($part, -5, 'تالف');
})->throws(InvalidArgumentException::class);

test('deducts and restores stock for a repair order', function () {
    $service = app(InventoryService::class);
    $order = RepairOrder::factory()->create();
    $part = SparePart::factory()->create(['quantity' => 10]);

    RepairOrderPart::factory()->create([
        'repair_order_id' => $order->id,
        'spare_part_id' => $part->id,
        'quantity' => 3,
    ]);

    $service->deductForRepairOrder($order->fresh());

    expect((int) $part->fresh()->quantity)->toBe(7);

    $service->restore($order->fresh());

    expect((int) $part->fresh()->quantity)->toBe(10);
});

test('detects low stock via the service and the scope', function () {
    $service = app(InventoryService::class);
    $low = SparePart::factory()->create(['quantity' => 5, 'minimum_stock' => 5]);
    $ok = SparePart::factory()->create(['quantity' => 6, 'minimum_stock' => 5]);

    expect($service->isLowStock($low))->toBeTrue()
        ->and($service->isLowStock($ok))->toBeFalse()
        ->and(SparePart::query()->lowStock()->pluck('id')->all())->toBe([$low->id]);
});
