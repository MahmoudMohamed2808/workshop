<?php

use App\Filament\Pages\RepairOrdersBoard;
use App\Filament\Widgets\LowStockPartsWidget;
use App\Filament\Widgets\TodayOverviewWidget;
use App\Models\Invoice;
use App\Models\Payment;
use App\Models\RepairOrder;
use App\Models\SparePart;
use Livewire\Livewire;

test('board renders one column per workflow stage', function () {
    RepairOrder::factory()->create(['status' => 'received']);

    Livewire::test(RepairOrdersBoard::class)
        ->assertOk()
        ->assertSee(__('dashboard.board.description'))
        ->assertSee(RepairOrder::first()->order_number);
});

test('board drag moves an order along an allowed transition', function () {
    $order = RepairOrder::factory()->create(['status' => 'received']);

    Livewire::test(RepairOrdersBoard::class)
        ->call('move', $order->id, 'in_repair');

    expect($order->fresh()->status)->toBe('in_repair');
});

test('board drag refuses a transition the workflow does not allow', function () {
    $order = RepairOrder::factory()->create(['status' => 'received']);

    Livewire::test(RepairOrdersBoard::class)
        ->call('move', $order->id, 'ready_for_delivery');

    expect($order->fresh()->status)->toBe('received');
});

test('board drag to delivered stays on the confirmation action', function () {
    $order = RepairOrder::factory()->create(['status' => 'ready_for_delivery']);

    Livewire::test(RepairOrdersBoard::class)
        ->call('move', $order->id, 'delivered');

    expect($order->fresh()->status)->toBe('ready_for_delivery');
});

test('overview widget shows counts and money figures', function () {
    RepairOrder::factory()->create(['status' => 'in_repair']);
    RepairOrder::factory()->create(['status' => 'delivered', 'delivered_at' => now()]);

    $invoice = Invoice::factory()->create(['total' => 1000, 'issued_at' => now()]);
    Payment::factory()->create(['invoice_id' => $invoice->id, 'amount' => 400, 'paid_at' => now()]);

    Livewire::test(TodayOverviewWidget::class)
        ->assertOk()
        ->assertSee(__('dashboard.overview.in_repair'))
        ->assertSee(__('dashboard.overview.delivered_today'))
        ->assertSee(number_format(1000, 2))
        ->assertSee(number_format(400, 2));
});

test('low stock widget lists only low parts', function () {
    $low = SparePart::factory()->create(['quantity' => 2, 'minimum_stock' => 5]);
    $healthy = SparePart::factory()->create(['quantity' => 20, 'minimum_stock' => 5]);

    Livewire::test(LowStockPartsWidget::class)
        ->assertOk()
        ->assertSee($low->code)
        ->assertDontSee($healthy->code);
});
