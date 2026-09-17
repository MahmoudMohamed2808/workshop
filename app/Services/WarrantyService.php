<?php

namespace App\Services;

use App\Models\Customer;
use App\Models\RepairOrder;
use App\Models\Warranty;

class WarrantyService
{
    public function createForRepairOrder(RepairOrder $order, int $days): Warranty
    {
        return Warranty::updateOrCreate(
            ['repair_order_id' => $order->id],
            [
                'duration_days' => $days,
                'starts_at' => today()->toDateString(),
                'ends_at' => today()->addDays($days)->toDateString(),
            ]
        );
    }

    public function findActiveWarranty(Customer $customer, ?string $serialNumber): ?Warranty
    {
        return Warranty::query()
            ->where('ends_at', '>=', today()->toDateString())
            ->whereHas('repairOrder', function ($query) use ($customer, $serialNumber) {
                $query->where('customer_id', $customer->id);

                if ($serialNumber !== null && trim($serialNumber) !== '') {
                    $query->where('serial_number', $serialNumber);
                }
            })
            ->latest('ends_at')
            ->first();
    }

    public function isUnderWarranty(Customer $customer, ?string $serialNumber): bool
    {
        return $this->findActiveWarranty($customer, $serialNumber) !== null;
    }
}
