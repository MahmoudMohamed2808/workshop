<?php

namespace App\Services;

use App\Models\RepairOrder;
use App\Models\SparePart;
use InvalidArgumentException;

class InventoryService
{
    public function adjustStock(SparePart $part, int $delta, string $reason): void
    {
        if (trim($reason) === '') {
            throw new InvalidArgumentException('A reason is required for stock adjustments.');
        }

        if ($delta === 0) {
            throw new InvalidArgumentException('The adjustment quantity cannot be zero.');
        }

        if ($part->quantity + $delta < 0) {
            throw new InvalidArgumentException(
                "Not enough stock for [{$part->code}]: cannot remove {$delta} from {$part->quantity}."
            );
        }

        $part->increment('quantity', $delta);
    }

    public function deductForRepairOrder(RepairOrder $order): void
    {
        foreach ($order->orderParts as $orderPart) {
            $this->adjustStock(
                $orderPart->sparePart,
                -$orderPart->quantity,
                "Deducted for repair order [{$order->order_number}]."
            );
        }
    }

    public function restore(RepairOrder $order): void
    {
        foreach ($order->orderParts as $orderPart) {
            $this->adjustStock(
                $orderPart->sparePart,
                $orderPart->quantity,
                "Restored from repair order [{$order->order_number}]."
            );
        }
    }

    public function isLowStock(SparePart $part): bool
    {
        return $part->quantity <= $part->minimum_stock;
    }
}
