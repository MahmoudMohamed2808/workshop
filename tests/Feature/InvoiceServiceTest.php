<?php

use App\Models\Problem;
use App\Models\RepairOrder;
use App\Models\RepairOrderPart;
use App\Models\RepairOrderProblem;
use App\Models\SparePart;
use App\Services\InvoiceService;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

function billableOrder(): RepairOrder
{
    $order = RepairOrder::factory()->create([
        'labor_cost' => 200,
        'additional_cost' => 50,
        'discount' => 100,
    ]);

    $problem = Problem::factory()->create();

    RepairOrderProblem::factory()->create([
        'repair_order_id' => $order->id,
        'problem_id' => $problem->id,
        'cost' => 500,
    ]);

    $part = SparePart::factory()->create(['selling_price' => 150]);

    RepairOrderPart::factory()->create([
        'repair_order_id' => $order->id,
        'spare_part_id' => $part->id,
        'quantity' => 2,
        'unit_price' => 150,
    ]);

    return $order;
}

test('generates invoice lines and totals from the repair order', function () {
    $invoice = app(InvoiceService::class)->generateFromRepairOrder(billableOrder());

    expect($invoice->items)->toHaveCount(5)
        ->and((float) $invoice->subtotal)->toBe(1050.0)
        ->and((float) $invoice->total)->toBe(950.0)
        ->and((float) $invoice->paid_total)->toBe(0.0)
        ->and((float) $invoice->remaining_total)->toBe(950.0)
        ->and($invoice->issued_at)->not->toBeNull();
});

test('refuses a second invoice for the same order', function () {
    $order = billableOrder();

    app(InvoiceService::class)->generateFromRepairOrder($order);
    app(InvoiceService::class)->generateFromRepairOrder($order);
})->throws(InvalidArgumentException::class);

test('add item recalculates totals', function () {
    $service = app(InvoiceService::class);
    $invoice = $service->generateFromRepairOrder(billableOrder());

    $service->addItem($invoice, [
        'description' => 'كشف إضافي',
        'type' => 'additional',
        'quantity' => 1,
        'unit_price' => 100,
        'total' => 100,
    ]);

    expect((float) $invoice->fresh()->total)->toBe(1050.0);
});

test('generates and attaches the invoice pdf', function () {
    $service = app(InvoiceService::class);
    $invoice = $service->generateFromRepairOrder(billableOrder());

    $media = $service->generatePdf($invoice);

    expect($media->file_name)->toBe($invoice->invoice_number.'.pdf')
        ->and($invoice->getFirstMedia('invoice_pdf')?->getPath())->toBeFile();
});
