<?php

namespace App\Services;

use App\Models\Invoice;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use InvalidArgumentException;

class PaymentService
{
    public function recordPayment(Invoice $invoice, array $data): Payment
    {
        return DB::transaction(function () use ($invoice, $data) {
            $invoice = $invoice->fresh() ?? $invoice;

            $amount = (float) ($data['amount'] ?? 0);

            if ($amount <= 0) {
                throw new InvalidArgumentException('Payment amount must be greater than zero.');
            }

            if ($amount > (float) $invoice->remaining_total) {
                throw new InvalidArgumentException('Payment amount exceeds the remaining balance.');
            }

            $payment = $invoice->payments()->create([
                'amount' => $amount,
                'method' => $data['method'] ?? 'cash',
                'reference_number' => $data['reference_number'] ?? null,
                'paid_at' => $data['paid_at'] ?? now(),
                'notes' => $data['notes'] ?? null,
                'received_by' => $data['received_by'] ?? null,
            ]);

            $this->refreshBalance($invoice->fresh() ?? $invoice);

            return $payment;
        });
    }

    public function recordFullPayment(Invoice $invoice, array $data = []): Payment
    {
        $invoice = $invoice->fresh() ?? $invoice;

        $remaining = (float) $invoice->remaining_total;

        if ($remaining <= 0) {
            throw new InvalidArgumentException('This invoice is already fully paid.');
        }

        return $this->recordPayment($invoice, [...$data, 'amount' => $remaining]);
    }

    public function refreshBalance(Invoice $invoice): void
    {
        $invoice->paid_total = $invoice->payments()->sum('amount');
        $invoice->remaining_total = max(0, (float) $invoice->total - (float) $invoice->paid_total);
        $invoice->save();
    }
}
