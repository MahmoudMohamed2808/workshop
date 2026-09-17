<?php

namespace App\Services;

use App\Models\RepairOrder;

class CostCalculatorService
{
    public function recalculateOrderTotal(RepairOrder $order): void
    {
        $order->total_cost = max(0, $this->computeSubtotal($order) - (float) $order->discount);
        $order->save();
    }

    public function computeSubtotal(RepairOrder $order): float
    {
        $problemsTotal = (float) $order->orderProblems()->sum('cost');

        $partsTotal = (float) $order->orderParts()
            ->selectRaw('COALESCE(SUM(quantity * unit_price), 0) as aggregate')
            ->value('aggregate');

        return $problemsTotal
            + $partsTotal
            + (float) $order->labor_cost
            + (float) $order->additional_cost;
    }
}
