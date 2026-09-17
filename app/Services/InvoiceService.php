<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\RepairOrder;
use App\Models\RepairOrderPart;
use App\Models\RepairOrderProblem;
use ArPHP\I18N\Arabic;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use InvalidArgumentException;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class InvoiceService
{
    public function generateFromRepairOrder(RepairOrder $order): Invoice
    {
        if ($order->invoice()->exists()) {
            throw new InvalidArgumentException('This repair order already has an invoice.');
        }

        $attempts = 0;

        do {
            try {
                return DB::transaction(function () use ($order) {
                    // Re-check inside the transaction: a concurrent request may
                    // have created the invoice after the early check above.
                    if ($order->invoice()->exists()) {
                        throw new InvalidArgumentException('This repair order already has an invoice.');
                    }

                    $invoice = Invoice::create([
                        'invoice_number' => $this->nextInvoiceNumber(),
                        'repair_order_id' => $order->id,
                        'issued_at' => now(),
                    ]);

                    $problems = RepairOrderProblem::with('problem')
                        ->where('repair_order_id', $order->id)
                        ->get();

                    foreach ($problems as $orderProblem) {
                        $this->addItem($invoice, [
                            'description' => $orderProblem->problem->name
                                .($orderProblem->notes ? ' — '.$orderProblem->notes : ''),
                            'type' => 'problem',
                            'quantity' => 1,
                            'unit_price' => $orderProblem->cost,
                            'total' => $orderProblem->cost,
                        ]);
                    }

                    $parts = RepairOrderPart::with('sparePart')
                        ->where('repair_order_id', $order->id)
                        ->get();

                    foreach ($parts as $orderPart) {
                        $this->addItem($invoice, [
                            'description' => $orderPart->sparePart->name
                                .' ('.$orderPart->sparePart->code.')',
                            'type' => 'part',
                            'quantity' => $orderPart->quantity,
                            'unit_price' => $orderPart->unit_price,
                            'total' => $orderPart->quantity * $orderPart->unit_price,
                        ]);
                    }

                    if ($order->labor_cost > 0) {
                        $this->addItem($invoice, [
                            'description' => __('invoices.items.labor'),
                            'type' => 'labor',
                            'quantity' => 1,
                            'unit_price' => $order->labor_cost,
                            'total' => $order->labor_cost,
                        ]);
                    }

                    if ($order->additional_cost > 0) {
                        $this->addItem($invoice, [
                            'description' => __('invoices.items.additional'),
                            'type' => 'additional',
                            'quantity' => 1,
                            'unit_price' => $order->additional_cost,
                            'total' => $order->additional_cost,
                        ]);
                    }

                    if ($order->discount > 0) {
                        $this->addItem($invoice, [
                            'description' => __('invoices.items.discount'),
                            'type' => 'discount',
                            'quantity' => 1,
                            'unit_price' => $order->discount,
                            'total' => -$order->discount,
                        ]);
                    }

                    $this->recalculateTotals($invoice);

                    return $invoice->fresh();
                });
            } catch (QueryException $e) {
                // Concurrent request already created the invoice for this order.
                if ($order->invoice()->exists()) {
                    throw new InvalidArgumentException('This repair order already has an invoice.');
                }

                // Concurrent request took our generated number: retry with the
                // next one. Anything else is a real failure — rethrow it.
                if (! str_contains($e->getMessage(), 'invoices.invoice_number')) {
                    throw $e;
                }
            }
        } while (++$attempts < 5);

        throw new InvalidArgumentException('Could not generate a unique invoice number, please try again.');
    }

    /**
     * Next sequential number for the current year (INV-YYYY-NNNNNN).
     *
     * Numbers must be sequential, never derived from the order id: historic
     * invoices already occupy numbers that would otherwise collide with
     * order-id-based candidates (e.g. order 161 vs invoice #161).
     */
    protected function nextInvoiceNumber(): string
    {
        $prefix = sprintf('INV-%s-', now()->format('Y'));

        $max = Invoice::where('invoice_number', 'like', $prefix.'%')->max('invoice_number');

        $next = $max === null ? 1 : ((int) substr((string) $max, strlen($prefix)) + 1);

        do {
            $candidate = sprintf('%s%06d', $prefix, $next);
            $next++;
        } while (Invoice::where('invoice_number', $candidate)->exists());

        return $candidate;
    }

    public function addItem(Invoice $invoice, array $itemData): InvoiceItem
    {
        $item = $invoice->items()->create($itemData);

        $this->recalculateTotals($invoice);

        return $item;
    }

    public function recalculateTotals(Invoice $invoice): void
    {
        $items = $invoice->items()->get();

        $subtotal = $items->where('type', '!=', 'discount')->sum('total');
        $discountLines = abs($items->where('type', 'discount')->sum('total'));

        $invoice->subtotal = $subtotal;
        $invoice->total = $subtotal - $discountLines - $invoice->discount;
        $invoice->remaining_total = $invoice->total - $invoice->paid_total;
        $invoice->save();
    }

    public function recalculatePayments(Invoice $invoice): void
    {
        $invoice->paid_total = $invoice->payments()->sum('amount');
        $invoice->remaining_total = max(0, (float) $invoice->total - (float) $invoice->paid_total);
        $invoice->save();
    }

    public function exportPdf(Invoice $invoice): string
    {
        $invoice->loadMissing(['items']);

        $order = $invoice->repairOrder()->firstOrFail();
        $order->loadMissing([
            'customer',
            'deviceType',
            'brand',
            'deviceModel',
            'technician',
            'orderProblems.problem',
            'orderParts.sparePart',
        ]);

        $html = view('invoices.sale-print', [
            'order' => $order,
            'invoice' => $invoice,
        ])->render();

        // dompdf cannot shape Arabic script on its own; pre-shape the
        // glyphs so letters join correctly in the rendered PDF.
        $shaped = (new Arabic)->utf8Glyphs($html);

        $pdf = Pdf::loadHTML($shaped)->setPaper('a5');

        $path = 'invoices/'.$invoice->invoice_number.'.pdf';
        Storage::disk('public')->put($path, $pdf->output());

        return Storage::disk('public')->url($path);
    }

    public function generatePdf(Invoice $invoice): Media
    {
        $invoice->loadMissing(['items', 'repairOrder.customer']);

        $html = view('invoices.pdf', ['invoice' => $invoice])->render();

        // dompdf cannot shape Arabic script on its own; pre-shape the
        // glyphs so letters join correctly in the rendered PDF.
        $shaped = (new Arabic)->utf8Glyphs($html);

        $pdf = Pdf::loadHTML($shaped)->setPaper('a4');

        return $invoice
            ->addMediaFromString($pdf->output())
            ->usingFileName($invoice->invoice_number.'.pdf')
            ->toMediaCollection('invoice_pdf');
    }
}
