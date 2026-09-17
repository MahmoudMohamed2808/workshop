<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\RepairOrder;
use Illuminate\Database\Seeder;

class InvoiceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * فاتورة لكل أمر عدى مرحلة الموافقة. البنود بتتركب في InvoiceItemSeeder
     * عشان كل سيدر يبقى مسؤول عن موديله، وهنا بنجهز الهيكل بس.
     */
    public function run(): void
    {
        if (Invoice::count() > 0) {
            return;
        }

        $eligible = ['in_repair', 'ready_for_delivery', 'delivered'];
        $counter = 1;

        RepairOrder::whereIn('status', $eligible)->orderBy('id')->chunkById(100, function ($orders) use (&$counter): void {
            foreach ($orders as $order) {
                // 92% من الأوامر المؤهلة ليها فاتورة (الباقي لسه متعملوش)
                if (random_int(1, 100) > 92) {
                    continue;
                }

                $issuedAt = $order->approved_at ?? $order->created_at;
                $issuedAt = $issuedAt->copy()->addHours(random_int(2, 72));

                Invoice::create([
                    'invoice_number' => sprintf('INV-2026-%06d', $counter++),
                    'repair_order_id' => $order->id,
                    'subtotal' => 0,
                    // الخصم بيتسجل كسطر بند (discount) زي InvoiceService بالظبط
                    'discount' => 0,
                    'total' => 0,
                    'paid_total' => 0,
                    'remaining_total' => 0,
                    'issued_at' => $issuedAt,
                ]);
            }
        });
    }
}
