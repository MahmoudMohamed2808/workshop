<?php

namespace App\Filament\Resources\RepairOrders;

class RepairOrderStatus
{
    public const RECEIVED = 'received';

    public const IN_REPAIR = 'in_repair';

    public const READY_FOR_DELIVERY = 'ready_for_delivery';

    public const DELIVERED = 'delivered';

    public const CANCELLED = 'cancelled';

    public const BEYOND_REPAIR = 'beyond_repair';

    /**
     * Main happy-path flow shown in the view-page stepper.
     *
     * @return list<string>
     */
    public static function flow(): array
    {
        return [
            self::RECEIVED,
            self::IN_REPAIR,
            self::READY_FOR_DELIVERY,
            self::DELIVERED,
        ];
    }

    /**
     * @return list<string>
     */
    public static function all(): array
    {
        return [
            ...self::flow(),
            self::CANCELLED,
            self::BEYOND_REPAIR,
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
        return self::statusLabel($status);
    }

    public static function statusLabel(string $status): string
    {
        return __("resources.repair_orders.statuses.{$status}");
    }

    public static function color(string $status): string
    {
        return match ($status) {
            self::RECEIVED => 'gray',
            self::IN_REPAIR => 'primary',
            self::READY_FOR_DELIVERY, self::DELIVERED => 'success',
            default => 'danger',
        };
    }

    public static function isTerminal(string $status): bool
    {
        return in_array($status, [self::DELIVERED, self::CANCELLED, self::BEYOND_REPAIR], true);
    }
}
