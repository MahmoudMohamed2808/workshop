<?php

namespace App\Filament\Resources\RepairOrders\Actions;

use App\Models\RepairOrder;
use App\Models\User;
use Filament\Actions\Action;

class PrintSaleInvoiceAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'printSaleInvoice';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (RepairOrder $record): string => $record->invoice()->exists()
                ? __('resources.repair_orders.actions.print_sale_invoice.print_label')
                : __('resources.repair_orders.actions.print_sale_invoice.label'))
            ->icon('heroicon-o-printer')
            ->color('primary')
            ->visible(fn (RepairOrder $record): bool => ($record->invoice()->exists()
                    || in_array($record->status, ['ready_for_delivery', 'delivered'], true))
                // الفني لا يطبع فواتير البيع.
                && ! (auth()->user() instanceof User && auth()->user()->isTechnician()))
            ->url(fn (RepairOrder $record): string => route('repair-orders.sale-print', $record))
            ->openUrlInNewTab();
    }
}
