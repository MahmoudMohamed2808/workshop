<?php

use App\Filament\Resources\RepairOrders\PaymentStatus;
use App\Models\Invoice;
use App\Models\RepairOrder;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function buildPayableInvoice(): Invoice
{
    $order = RepairOrder::factory()->create([
        'labor_cost' => 1000,
        'additional_cost' => 0,
        'discount' => 0,
    ]);

    return app(InvoiceService::class)->generateFromRepairOrder($order);
}

test('new invoice is unpaid', function () {
    $invoice = buildPayableInvoice();

    expect($invoice->payment_status)->toBe(PaymentStatus::UNPAID)
        ->and($invoice->is_fully_paid)->toBeFalse();
});

test('partial payment marks the invoice partial', function () {
    $invoice = buildPayableInvoice();

    app(PaymentService::class)->recordPayment($invoice, ['amount' => 400]);

    expect($invoice->fresh()->payment_status)->toBe(PaymentStatus::PARTIAL);
});

test('full payment marks the invoice paid', function () {
    $invoice = buildPayableInvoice();

    app(PaymentService::class)->recordPayment($invoice, ['amount' => 1000]);

    $invoice = $invoice->fresh();

    expect($invoice->payment_status)->toBe(PaymentStatus::PAID)
        ->and($invoice->is_fully_paid)->toBeTrue();
});

test('repair order without invoice reports no_invoice', function () {
    $order = RepairOrder::factory()->create();

    expect($order->payment_status)->toBe(PaymentStatus::NO_INVOICE);
});

test('repair order delegates payment status to its invoice', function () {
    $invoice = buildPayableInvoice();
    $order = $invoice->repairOrder;

    expect($order->payment_status)->toBe(PaymentStatus::UNPAID);

    app(PaymentService::class)->recordPayment($invoice, ['amount' => 1000]);

    expect($order->fresh()->payment_status)->toBe(PaymentStatus::PAID);
});

test('record full payment collects the remaining balance', function () {
    $invoice = buildPayableInvoice();
    $service = app(PaymentService::class);

    $service->recordPayment($invoice, ['amount' => 300]);
    $service->recordFullPayment($invoice->fresh(), ['method' => 'cash']);

    $invoice = $invoice->fresh();

    expect((float) $invoice->paid_total)->toBe(1000.0)
        ->and((float) $invoice->remaining_total)->toBe(0.0)
        ->and($invoice->payment_status)->toBe(PaymentStatus::PAID);
});

test('record full payment fails when already paid', function () {
    $invoice = buildPayableInvoice();

    app(PaymentService::class)->recordPayment($invoice, ['amount' => 1000]);

    app(PaymentService::class)->recordFullPayment($invoice->fresh());
})->throws(InvalidArgumentException::class);

test('partial payment leaves the correct remaining balance', function () {
    $invoice = buildPayableInvoice();

    app(PaymentService::class)->recordPayment($invoice, ['amount' => 300]);

    $invoice = $invoice->fresh();

    expect((float) $invoice->paid_total)->toBe(300.0)
        ->and((float) $invoice->remaining_total)->toBe(700.0)
        ->and($invoice->payment_status)->toBe(PaymentStatus::PARTIAL);
});

test('editing a payment updates the balance on recalculation', function () {
    $invoice = buildPayableInvoice();

    $payment = app(PaymentService::class)->recordPayment($invoice, ['amount' => 400]);
    $payment->update(['amount' => 200]);

    app(InvoiceService::class)->recalculatePayments($invoice->fresh());

    $invoice = $invoice->fresh();

    expect((float) $invoice->paid_total)->toBe(200.0)
        ->and((float) $invoice->remaining_total)->toBe(800.0)
        ->and($invoice->payment_status)->toBe(PaymentStatus::PARTIAL);
});

test('payment status scope filters repair orders', function () {
    $unpaidInvoice = buildPayableInvoice();
    $paidInvoice = buildPayableInvoice();

    app(PaymentService::class)->recordPayment($paidInvoice, ['amount' => 1000]);

    $paid = RepairOrder::query()->wherePaymentStatus(PaymentStatus::PAID)->pluck('id')->all();
    $unpaid = RepairOrder::query()->wherePaymentStatus(PaymentStatus::UNPAID)->pluck('id')->all();

    expect($paid)->toContain($paidInvoice->repair_order_id)
        ->and($paid)->not->toContain($unpaidInvoice->repair_order_id)
        ->and($unpaid)->toContain($unpaidInvoice->repair_order_id)
        ->and($unpaid)->not->toContain($paidInvoice->repair_order_id);
});
