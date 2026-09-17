<?php

use App\Filament\Resources\RepairOrders\Pages\ViewRepairOrder;
use App\Filament\Resources\RepairOrders\PaymentStatus;
use App\Filament\Resources\RepairOrders\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\RepairOrders\RepairOrderResource;
use App\Models\RepairOrder;
use App\Models\User;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

beforeEach(function () {
    seedPanelRoles();
});

function viewableOrderWithBalance(float $laborCost, float $paidAmount): RepairOrder
{
    $order = RepairOrder::factory()->create([
        'labor_cost' => $laborCost,
        'additional_cost' => 0,
        'discount' => 0,
    ]);

    $invoice = app(InvoiceService::class)->generateFromRepairOrder($order);

    if ($paidAmount > 0) {
        app(PaymentService::class)->recordPayment($invoice, ['amount' => $paidAmount]);
    }

    return $order->fresh();
}

function testPaymentsManager(User $user, RepairOrder $order)
{
    // موظف الاستقبال/الكاشير: المدفوعات تُدار بصلاحية ManagePayments.
    return Livewire::actingAs($user)->test(PaymentsRelationManager::class, [
        'ownerRecord' => $order,
        'pageClass' => ViewRepairOrder::class,
    ]);
}

test('view page shell shows the payments section', function () {
    $user = staffPanelUser();
    $order = viewableOrderWithBalance(1000, 300);

    $this->actingAs($user)
        ->get(RepairOrderResource::getUrl('view', ['record' => $order]))
        ->assertOk()
        ->assertSee(__('resources.repair_orders.relations.payments.title'), false);
});

test('payments section renders records with live balances and actions', function () {
    $user = staffPanelUser();
    $order = viewableOrderWithBalance(1000, 300);

    testPaymentsManager($user, $order)
        ->assertOk()
        ->assertSee(number_format(300, 2))
        ->assertSee(number_format(700, 2))
        ->assertSee(number_format(1000, 2))
        ->assertTableHeaderActionsExistInOrder(['create', 'collectRemaining'])
        ->assertTableActionEnabled('create')
        ->assertTableActionEnabled('collectRemaining');
});

test('collect remaining collects the leftover and marks the invoice paid', function () {
    $user = staffPanelUser();
    $order = viewableOrderWithBalance(1000, 300);

    testPaymentsManager($user, $order)
        ->callTableAction('collectRemaining', null, [
            'method' => 'cash',
            'paid_at' => now()->format('Y-m-d H:i:s'),
        ])
        ->assertHasNoTableActionErrors();

    $invoice = $order->invoice->fresh();

    expect((float) $invoice->paid_total)->toBe(1000.0)
        ->and((float) $invoice->remaining_total)->toBe(0.0)
        ->and($invoice->payment_status)->toBe(PaymentStatus::PAID);
});

test('creating a payment above the remaining balance fails validation', function () {
    $user = staffPanelUser();
    $order = viewableOrderWithBalance(1000, 300);

    testPaymentsManager($user, $order)
        ->callTableAction('create', null, [
            'amount' => 800,
            'method' => 'cash',
            'paid_at' => now()->format('Y-m-d H:i:s'),
        ])
        ->assertHasTableActionErrors(['amount']);

    expect((float) $order->invoice->fresh()->paid_total)->toBe(300.0);
});

test('payment actions disable once the invoice is fully paid', function () {
    $user = staffPanelUser();
    $order = viewableOrderWithBalance(1000, 1000);

    testPaymentsManager($user, $order)
        ->assertOk()
        ->assertSee(__('resources.repair_orders.relations.payments.summary', [
            'paid' => number_format(1000, 2),
            'remaining' => number_format(0, 2),
            'total' => number_format(1000, 2),
        ]), false)
        ->assertTableActionDisabled('create')
        ->assertTableActionDisabled('collectRemaining');
});

test('payments section shows the no-invoice hint when no invoice exists', function () {
    $user = staffPanelUser();
    $order = RepairOrder::factory()->create();

    testPaymentsManager($user, $order)
        ->assertOk()
        ->assertSee(__('resources.repair_orders.relations.payments.no_invoice_hint'), false)
        ->assertTableActionDisabled('create')
        ->assertTableActionDisabled('collectRemaining');
});
