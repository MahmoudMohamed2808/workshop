<?php

namespace App\Services;

use App\Models\RepairOrder;
use InvalidArgumentException;

class RepairOrderWorkflowService
{
    /**
     * Allowed next statuses for every status. Terminal states
     * (delivered, rejected, cancelled, beyond_repair) have no exits.
     *
     * @var array<string, list<string>>
     */
    protected array $transitions = [
        'received' => ['in_repair', 'cancelled'],
        'in_repair' => ['ready_for_delivery', 'beyond_repair', 'cancelled'],
        'ready_for_delivery' => ['delivered'],
        'delivered' => [],
        'cancelled' => [],
        'beyond_repair' => [],
    ];

    public function __construct(protected WarrantyService $warranties) {}

    public function transitionTo(RepairOrder $order, string $status): void
    {
        if (! $this->canTransitionTo($order, $status)) {
            throw new InvalidArgumentException(
                "Cannot transition repair order from [{$order->status}] to [{$status}]."
            );
        }

        $order->status = $status;
        $order->save();
    }

    public function canTransitionTo(RepairOrder $order, string $status): bool
    {
        return in_array($status, $this->transitions[$order->status] ?? [], true);
    }

    public function cancel(RepairOrder $order, ?string $reason = null): void
    {
        if ($reason !== null && trim($reason) !== '') {
            $order->rejection_reason = $reason;
        }

        $this->transitionTo($order, 'cancelled');
    }

    public static function isStockDeducted(string $status): bool
    {
        return in_array($status, ['in_repair', 'ready_for_delivery', 'delivered'], true);
    }

    public function markBeyondRepair(RepairOrder $order, string $reason): void
    {
        if (trim($reason) === '') {
            throw new InvalidArgumentException('A beyond-repair reason is required.');
        }

        $order->rejection_reason = $reason;

        $this->transitionTo($order, 'beyond_repair');
    }

    public function confirmDelivery(RepairOrder $order, ?string $confirmedBy = null, int $warrantyDays = 30): void
    {
        $order->delivered_at = now();

        $this->transitionTo($order, 'delivered');

        $this->warranties->createForRepairOrder($order, $warrantyDays);
    }
}
