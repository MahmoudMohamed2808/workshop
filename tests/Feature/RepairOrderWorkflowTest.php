<?php

use App\Models\RepairOrder;
use App\Services\RepairOrderWorkflowService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function workflow(): RepairOrderWorkflowService
{
    return app(RepairOrderWorkflowService::class);
}

function orderWithStatus(string $status): RepairOrder
{
    return RepairOrder::factory()->create(['status' => $status]);
}

test('allows each forward step of the repair workflow', function (string $from, string $to) {
    $order = orderWithStatus($from);

    workflow()->transitionTo($order, $to);

    expect($order->fresh()->status)->toBe($to);
})->with([
    ['received', 'in_repair'],
    ['in_repair', 'ready_for_delivery'],
    ['ready_for_delivery', 'delivered'],
]);

test('allows exception states from their earlier stages', function (string $from, string $to) {
    $order = orderWithStatus($from);

    workflow()->transitionTo($order, $to);

    expect($order->fresh()->status)->toBe($to);
})->with([
    ['received', 'cancelled'],
    ['in_repair', 'cancelled'],
    ['in_repair', 'beyond_repair'],
]);

test('rejects transitions outside the graph', function (string $from, string $to) {
    $order = orderWithStatus($from);

    workflow()->transitionTo($order, $to);
})->with([
    ['received', 'ready_for_delivery'],
    ['received', 'delivered'],
    ['received', 'beyond_repair'],
    ['in_repair', 'delivered'],
    ['received', 'unknown_status'],
    ['delivered', 'received'],
    ['cancelled', 'received'],
    ['beyond_repair', 'received'],
    ['ready_for_delivery', 'cancelled'],
])->throws(InvalidArgumentException::class);

test('cancel stores an optional reason', function () {
    $order = orderWithStatus('received');

    workflow()->cancel($order, 'العميل لغى الطلب');

    $order = $order->fresh();

    expect($order->status)->toBe('cancelled')
        ->and($order->rejection_reason)->toBe('العميل لغى الطلب');
});

test('marking beyond repair refuses an empty reason', function () {
    $order = orderWithStatus('in_repair');

    workflow()->markBeyondRepair($order, '   ');
})->throws(InvalidArgumentException::class);

test('confirm delivery refuses an order that is not ready', function () {
    $order = orderWithStatus('in_repair');

    workflow()->confirmDelivery($order);
})->throws(InvalidArgumentException::class);
