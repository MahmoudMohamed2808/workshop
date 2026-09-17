<?php

namespace App\Filament\Resources\RepairOrders\Widgets;

use App\Filament\Resources\RepairOrders\PaymentStatus;
use App\Models\RepairOrder;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class RepairOrderPaymentSummary extends StatsOverviewWidget
{
    public ?RepairOrder $record = null;

    /**
     * @return array<int, Stat>
     */
    protected function getStats(): array
    {
        $invoice = $this->record?->invoice;
        $total = (float) ($invoice?->total ?? $this->record?->total_cost ?? 0);
        $paid = (float) ($invoice?->paid_total ?? 0);
        $remaining = (float) ($invoice?->remaining_total ?? $total);

        $status = $invoice === null
            ? PaymentStatus::NO_INVOICE
            : $invoice->payment_status;

        return [
            Stat::make(__('resources.repair_orders.payment_summary.total'), number_format($total, 2).' ج.م')
                ->icon('heroicon-o-document-text')
                ->color('gray'),
            Stat::make(__('resources.repair_orders.payment_summary.paid'), number_format($paid, 2).' ج.م')
                ->icon('heroicon-o-banknotes')
                ->color($paid > 0 ? 'success' : 'gray'),
            Stat::make(__('resources.repair_orders.payment_summary.remaining'), number_format($remaining, 2).' ج.م')
                ->description($invoice === null
                    ? __('resources.repair_orders.payment_summary.no_invoice')
                    : PaymentStatus::label($status))
                ->icon('heroicon-o-clock')
                ->color(PaymentStatus::color($status)),
        ];
    }
}
