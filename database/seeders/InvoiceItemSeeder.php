<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\InvoiceItem;
use Illuminate\Database\Seeder;

class InvoiceItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * بنود الفاتورة مطابقة لمنطق InvoiceService: الأعطال + قطع الغيار
     * + المصنعية + الإضافي + سطر الخصم، وبعدها إعادة حساب الإجماليات.
     */
    public function run(): void
    {
        if (InvoiceItem::count() > 0) {
            return;
        }

        Invoice::with(['repairOrder.orderProblems.problem', 'repairOrder.orderParts.sparePart'])
            ->chunkById(100, function ($invoices): void {
                foreach ($invoices as $invoice) {
                    if ($invoice->items()->exists()) {
                        continue;
                    }

                    $order = $invoice->repairOrder;
                    if (! $order) {
                        continue;
                    }

                    foreach ($order->orderProblems as $op) {
                        $invoice->items()->create([
                            'description' => $op->problem->name.($op->notes ? ' — '.$op->notes : ''),
                            'type' => 'problem',
                            'quantity' => 1,
                            'unit_price' => $op->cost,
                            'total' => $op->cost,
                        ]);
                    }

                    foreach ($order->orderParts as $part) {
                        $total = (float) $part->quantity * (float) $part->unit_price;
                        $invoice->items()->create([
                            'description' => $part->sparePart->name.' ('.$part->sparePart->code.')',
                            'type' => 'part',
                            'quantity' => $part->quantity,
                            'unit_price' => $part->unit_price,
                            'total' => $total,
                        ]);
                    }

                    if ((float) $order->labor_cost > 0) {
                        $invoice->items()->create([
                            'description' => 'أتعاب الصيانة والتركيب',
                            'type' => 'labor',
                            'quantity' => 1,
                            'unit_price' => $order->labor_cost,
                            'total' => $order->labor_cost,
                        ]);
                    }

                    if ((float) $order->additional_cost > 0) {
                        $invoice->items()->create([
                            'description' => 'مصاريف إضافية (انتقالات وشحن)',
                            'type' => 'additional',
                            'quantity' => 1,
                            'unit_price' => $order->additional_cost,
                            'total' => $order->additional_cost,
                        ]);
                    }

                    if ((float) $order->discount > 0) {
                        $invoice->items()->create([
                            'description' => 'خصم عميل مميز',
                            'type' => 'discount',
                            'quantity' => 1,
                            'unit_price' => $order->discount,
                            'total' => -((float) $order->discount),
                        ]);
                    }

                    // لو أمر بدون أي بنود (نادر)، سجل مصنعية رمزية عشان الفاتورة متبقاش صفر
                    if ($invoice->items()->count() === 0) {
                        $invoice->items()->create([
                            'description' => 'كشف وتشخيص',
                            'type' => 'labor',
                            'quantity' => 1,
                            'unit_price' => 150,
                            'total' => 150,
                        ]);
                    }

                    $this->recalculate($invoice);
                }
            });
    }

    private function recalculate(Invoice $invoice): void
    {
        // نفس معادلة App\Services\InvoiceService::recalculateTotals
        $items = $invoice->items()->get();
        $subtotal = $items->where('type', '!=', 'discount')->sum('total');
        $discountLines = abs($items->where('type', 'discount')->sum('total'));

        $invoice->subtotal = $subtotal;
        $invoice->total = max(0, $subtotal - $discountLines - (float) $invoice->discount);
        $invoice->remaining_total = (float) $invoice->total - (float) $invoice->paid_total;
        $invoice->timestamps = false;
        $invoice->save();
        $invoice->timestamps = true;
    }
}
