<?php

namespace App\Filament\Resources\RepairOrders\Actions;

use App\Models\RepairOrder;
use App\Services\InvoiceService;
use App\Services\WhatsappService;
use Filament\Actions\Action;
use Filament\Forms\Components\Checkbox;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;

class SendWhatsappUpdateAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'sendWhatsappUpdate';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (): string => __('resources.repair_orders.actions.send_whatsapp_update.label'))
            ->icon('heroicon-o-chat-bubble-left-ellipsis')
            ->color('success')
            ->modalHeading(fn (): string => __('resources.repair_orders.actions.send_whatsapp_update.heading'))
            ->modalSubmitActionLabel(fn (): string => __('resources.repair_orders.actions.send_whatsapp_update.submit'))
            ->form(fn (RepairOrder $record): array => [
                Select::make('type')
                    ->label(fn (): string => __('resources.repair_orders.actions.send_whatsapp_update.type'))
                    ->options(__('resources.customers.message_types'))
                    ->default('approval_request')
                    ->required()
                    ->live()
                    ->native(false),
                Textarea::make('message')
                    ->label(fn (): string => __('resources.repair_orders.actions.send_whatsapp_update.message'))
                    ->placeholder(fn (): string => __('resources.repair_orders.actions.send_whatsapp_update.message_placeholder'))
                    ->rows(4)
                    ->maxLength(1000)
                    ->visible(fn (Get $get): bool => $get('type') === 'other')
                    ->required(fn (Get $get): bool => $get('type') === 'other')
                    ->columnSpanFull(),
                Checkbox::make('attach_invoice')
                    ->label('إرفاق فاتورة الطلب')
                    ->visible($record->invoice()->exists())
                    ->default($record->invoice()->exists()),
            ])
            ->action(function (RepairOrder $record, Action $action, array $data, WhatsappService $whatsapp, InvoiceService $invoices): void {
                $invoice = $record->invoice;

                if (($data['attach_invoice'] ?? false) && $invoice !== null) {
                    $caption = trim($data['message'] ?? '');

                    if ($caption === '') {
                        $caption = 'فاتورتك جاهزة، يمكنك مراجعتها';
                    }

                    if (($customer = $record->customer()->first()) !== null) {
                        $whatsapp->sendMedia($customer, $invoices->exportPdf($invoice), $caption);
                    } else {
                        $action->halt();
                    }

                    return;
                }

                if (($customer = $record->customer()->first()) === null) {
                    $action->halt();

                    return;
                }

                if ($data['type'] === 'other') {
                    $whatsapp->send($customer, $data['message']);

                    return;
                }

                $device = trim(implode(' ', array_filter([
                    $record->deviceType()->first()?->name,
                    $record->brand()->first()?->name,
                ])));

                $whatsapp->sendTemplate($customer, $data['type'], [
                    'device' => $device !== '' ? $device : $record->order_number,
                    'order' => $record->order_number,
                    'diagnosis' => $record->technician_diagnosis ?? '',
                    'total' => $record->total_cost,
                    'amount' => $invoice !== null ? $invoice->remaining_total : $record->total_cost,
                    'message' => '',
                ], $record);
            })
            ->successNotification(fn (): Notification => Notification::make()
                ->success()
                ->title(__('resources.repair_orders.actions.send_whatsapp_update.success')));
    }
}
