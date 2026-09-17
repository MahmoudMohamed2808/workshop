<?php

namespace App\Filament\Resources\RepairOrders\Actions;

use App\Models\RepairOrder;
use App\Services\InvoiceService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class InvoiceAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'invoice';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (RepairOrder $record): string => $record->invoice()->exists()
                ? 'عرض / طباعة الفاتورة'
                : 'إصدار فاتورة')
            ->icon(fn (RepairOrder $record): string => $record->invoice()->exists()
                ? 'heroicon-o-printer'
                : 'heroicon-o-document-text')
            ->color(fn (RepairOrder $record): string => $record->invoice()->exists() ? 'primary' : 'gray')
            // إصدار/عرض الفواتير للموظف والإدارة فقط — الفني ممنوع.
            ->visible(fn (): bool => auth()->user()?->can('ManagePayments') ?? false)
            // Visible in every status — no status-based conditions.
            ->url(fn (RepairOrder $record): ?string => $record->invoice()->exists()
                ? route('repair-orders.sale-print', $record)
                : null)
            ->openUrlInNewTab()
            ->requiresConfirmation()
            ->modalHeading(fn (): string => __('resources.repair_orders.actions.generate_invoice.heading'))
            ->modalSubmitActionLabel(fn (): string => __('resources.repair_orders.actions.generate_invoice.submit'))
            ->action(function (RepairOrder $record, Action $action, InvoiceService $invoices): void {
                if ($record->invoice()->exists()) {
                    $action->halt();

                    return;
                }

                try {
                    abort_unless(auth()->user()?->can('ManagePayments') ?? false, 403);

                    $invoices->generateFromRepairOrder($record->refresh());
                } catch (\Throwable) {
                    Notification::make()
                        ->danger()
                        ->title(__('resources.repair_orders.actions.generate_invoice.already_exists'))
                        ->send();

                    $action->halt();
                }
            })
            ->successNotification(fn (): Notification => Notification::make()
                ->success()
                ->title(__('resources.repair_orders.actions.generate_invoice.success')));
    }
}
