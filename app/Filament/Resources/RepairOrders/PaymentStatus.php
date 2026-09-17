<?php

namespace App\Filament\Resources\RepairOrders;

class PaymentStatus
{
    public const NO_INVOICE = 'no_invoice';

    public const UNPAID = 'unpaid';

    public const PARTIAL = 'partial';

    public const PAID = 'paid';

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            self::NO_INVOICE,
            self::UNPAID,
            self::PARTIAL,
            self::PAID,
        ];
    }

    /**
     * @return array<string, string>
     */
    public static function options(): array
    {
        return collect(self::all())
            ->mapWithKeys(fn (string $status): array => [$status => self::label($status)])
            ->all();
    }

    public static function label(string $status): string
    {
        return __("resources.repair_orders.payment_statuses.{$status}");
    }

    public static function color(string $status): string
    {
        return match ($status) {
            self::PAID => 'success',
            self::PARTIAL => 'warning',
            self::UNPAID => 'danger',
            default => 'gray',
        };
    }

    public static function forInvoice(?float $total, ?float $paidTotal, ?float $remainingTotal): string
    {
        $paid = (float) ($paidTotal ?? 0);
        $remaining = (float) ($remainingTotal ?? ($total ?? 0));

        if ($remaining <= 0 && $paid > 0) {
            return self::PAID;
        }

        if ($paid > 0) {
            return self::PARTIAL;
        }

        return self::UNPAID;
    }
}
