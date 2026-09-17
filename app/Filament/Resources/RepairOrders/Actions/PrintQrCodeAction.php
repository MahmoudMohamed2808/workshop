<?php

namespace App\Filament\Resources\RepairOrders\Actions;

use App\Models\RepairOrder;
use App\Services\QrCodeService;
use Filament\Actions\Action;

class PrintQrCodeAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'printQrCode';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (): string => __('resources.repair_orders.actions.print_qr_code.label'))
            ->icon('heroicon-o-qr-code')
            ->color('gray')
            ->modalHeading(fn (RepairOrder $record): string => __('resources.repair_orders.actions.print_qr_code.heading')." — {$record->order_number}")
            ->modalSubmitActionLabel(fn (): string => __('resources.repair_orders.actions.print_qr_code.submit'))
            ->modalContent(fn (RepairOrder $record, QrCodeService $qrCodes) => view('filament.repair-orders.qr-code', [
                'order' => $record,
                'svg' => $qrCodes->generateForRepairOrder($record),
            ]))
            ->action(fn (): null => null);
    }
}
