<?php

use App\Models\Invoice;
use App\Models\RepairOrder;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function payableInvoice(): Invoice
{
    $order = RepairOrder::factory()->create([
        'labor_cost' => 1000,
        'additional_cost' => 0,
        'discount' => 0,
    ]);

    return app(InvoiceService::class)->generateFromRepairOrder($order);
}

test('records payment and keeps the balance in sync', function () {
    $invoice = payableInvoice();

    app(PaymentService::class)->recordPayment($invoice, ['amount' => 400]);

    $invoice = $invoice->fresh();

    expect((float) $invoice->paid_total)->toBe(400.0)
        ->and((float) $invoice->remaining_total)->toBe(600.0)
        ->and($invoice->is_fully_paid)->toBeFalse();
});

test('marks the invoice fully paid on full payment', function () {
    $invoice = payableInvoice();

    app(PaymentService::class)->recordPayment($invoice, ['amount' => 1000, 'method' => 'bank_transfer']);

    $invoice = $invoice->fresh();

    expect((float) $invoice->remaining_total)->toBe(0.0)
        ->and($invoice->is_fully_paid)->toBeTrue();
});

test('rejects payment above the remaining balance', function () {
    app(PaymentService::class)->recordPayment(payableInvoice(), ['amount' => 1001]);
})->throws(InvalidArgumentException::class);

test('rejects zero payment', function () {
    app(PaymentService::class)->recordPayment(payableInvoice(), ['amount' => 0]);
})->throws(InvalidArgumentException::class);

test('keeps totals correct across multiple partial payments', function () {
    $invoice = payableInvoice();
    $service = app(PaymentService::class);

    $service->recordPayment($invoice, ['amount' => 300]);

    expect((float) $invoice->fresh()->paid_total)->toBe(300.0)
        ->and((float) $invoice->fresh()->remaining_total)->toBe(700.0)
        ->and($invoice->fresh()->is_fully_paid)->toBeFalse();

    $service->recordPayment($invoice->fresh(), ['amount' => 300, 'method' => 'bank_transfer']);

    expect((float) $invoice->fresh()->paid_total)->toBe(600.0)
        ->and((float) $invoice->fresh()->remaining_total)->toBe(400.0)
        ->and($invoice->fresh()->is_fully_paid)->toBeFalse();

    $service->recordPayment($invoice->fresh(), ['amount' => 400]);

    $invoice = $invoice->fresh();

    expect((float) $invoice->paid_total)->toBe(1000.0)
        ->and((float) $invoice->remaining_total)->toBe(0.0)
        ->and($invoice->is_fully_paid)->toBeTrue()
        ->and($invoice->payments()->count())->toBe(3);
});
