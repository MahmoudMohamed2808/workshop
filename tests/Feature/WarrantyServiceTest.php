<?php

use App\Models\Customer;
use App\Models\RepairOrder;
use App\Models\Warranty;
use App\Services\RepairOrderWorkflowService;
use App\Services\WarrantyService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('creates warranty with correct dates', function () {
    $order = RepairOrder::factory()->create();

    $warranty = app(WarrantyService::class)->createForRepairOrder($order, 60);

    expect($warranty->duration_days)->toBe(60)
        ->and($warranty->starts_at->toDateString())->toBe(today()->toDateString())
        ->and($warranty->ends_at->toDateString())->toBe(today()->addDays(60)->toDateString());
});

test('finds active warranty by customer and serial', function () {
    $service = app(WarrantyService::class);
    $customer = Customer::factory()->create();
    $order = RepairOrder::factory()->create([
        'customer_id' => $customer->id,
        'serial_number' => 'SN123',
    ]);

    $service->createForRepairOrder($order, 30);

    expect($service->isUnderWarranty($customer, 'SN123'))->toBeTrue()
        ->and($service->isUnderWarranty($customer, 'OTHER'))->toBeFalse();
});

test('ignores expired warranties', function () {
    $service = app(WarrantyService::class);
    $customer = Customer::factory()->create();
    $order = RepairOrder::factory()->create(['customer_id' => $customer->id]);

    Warranty::factory()->create([
        'repair_order_id' => $order->id,
        'duration_days' => 30,
        'starts_at' => today()->subDays(60)->toDateString(),
        'ends_at' => today()->subDays(30)->toDateString(),
    ]);

    expect($service->isUnderWarranty($customer, null))->toBeFalse();
});

test('delivery creates the warranty', function () {
    $order = RepairOrder::factory()->create(['status' => 'ready_for_delivery']);

    app(RepairOrderWorkflowService::class)->confirmDelivery($order);

    $order = $order->fresh();

    expect($order->status)->toBe('delivered')
        ->and($order->delivered_at)->not->toBeNull()
        ->and($order->warranty)->not->toBeNull()
        ->and($order->warranty->duration_days)->toBe(30);
});

test('detects active versus expired warranty for the same customer and serial', function () {
    $service = app(WarrantyService::class);
    $customer = Customer::factory()->create();

    $expiredOrder = RepairOrder::factory()->create([
        'customer_id' => $customer->id,
        'serial_number' => 'SN-SAME',
    ]);

    Warranty::factory()->create([
        'repair_order_id' => $expiredOrder->id,
        'duration_days' => 30,
        'starts_at' => today()->subDays(60)->toDateString(),
        'ends_at' => today()->subDays(30)->toDateString(),
    ]);

    expect($service->isUnderWarranty($customer, 'SN-SAME'))->toBeFalse();

    $activeOrder = RepairOrder::factory()->create([
        'customer_id' => $customer->id,
        'serial_number' => 'SN-SAME',
    ]);

    $service->createForRepairOrder($activeOrder, 30);

    expect($service->isUnderWarranty($customer, 'SN-SAME'))->toBeTrue();
});
