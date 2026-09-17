<?php

namespace Database\Seeders;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceService;
use Illuminate\Database\Seeder;

class PaymentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * المسلم (delivered): 70% دفعة واحدة كاملة، 30% دفعتين (عربون + الباقي).
     * الجاهز للتسليم (ready_for_delivery): 60% دفعة جزئية (عربون)، 40% بدون دفع.
     */
    public function run(): void
    {
        if (Payment::count() > 0) {
            return;
        }

        $methods = ['cash', 'cash', 'cash', 'vodafone_cash', 'vodafone_cash', 'bank_transfer'];

        $notes = [
            'cash' => 'العميل دفع كاش وقت الاستلام',
            'vodafone_cash' => 'تحويل فودافون كاش',
            'bank_transfer' => 'تحويل بنكي',
        ];

        Invoice::with('repairOrder')
            ->whereHas('repairOrder', fn ($query) => $query->whereIn('status', ['delivered', 'ready_for_delivery']))
            ->orderBy('id')
            ->chunkById(100, function ($invoices) use ($methods, $notes): void {
                foreach ($invoices as $invoice) {
                    $total = (float) $invoice->total;

                    if ($total <= 0) {
                        continue;
                    }

                    $status = $invoice->repairOrder->status;
                    $roll = random_int(1, 100);
                    $issuedAt = $invoice->issued_at ?? $invoice->created_at;

                    if ($status === 'delivered') {
                        if ($roll <= 70) {
                            $method = $methods[array_rand($methods)];

                            $invoice->payments()->create([
                                'amount' => $total,
                                'method' => $method,
                                'reference_number' => $method === 'cash' ? null : strtoupper(uniqid('TX')),
                                'paid_at' => $issuedAt->copy()->addHours(random_int(1, 48)),
                                'notes' => $notes[$method],
                            ]);
                        } else {
                            $first = round($total * random_int(30, 50) / 100, 2);
                            $firstPaidAt = $issuedAt->copy()->addHours(random_int(1, 48));
                            $method = $methods[array_rand($methods)];

                            $invoice->payments()->create([
                                'amount' => $first,
                                'method' => $method,
                                'reference_number' => $method === 'cash' ? null : strtoupper(uniqid('TX')),
                                'paid_at' => $firstPaidAt,
                                'notes' => 'عربون تحت الحساب',
                            ]);

                            $method = $methods[array_rand($methods)];

                            $invoice->payments()->create([
                                'amount' => round($total - $first, 2),
                                'method' => $method,
                                'reference_number' => $method === 'cash' ? null : strtoupper(uniqid('TX')),
                                'paid_at' => $firstPaidAt->copy()->addHours(random_int(1, 72)),
                                'notes' => 'باقي الحساب عند التسليم',
                            ]);
                        }
                    } elseif ($roll <= 60) {
                        $method = $methods[array_rand($methods)];

                        $invoice->payments()->create([
                            'amount' => round($total * random_int(20, 50) / 100, 2),
                            'method' => $method,
                            'reference_number' => $method === 'cash' ? null : strtoupper(uniqid('TX')),
                            'paid_at' => $issuedAt->copy()->addHours(random_int(1, 72)),
                            'notes' => 'عربون تحت الحساب',
                        ]);
                    }

                    app(InvoiceService::class)->recalculatePayments($invoice);
                }
            });
    }
}
