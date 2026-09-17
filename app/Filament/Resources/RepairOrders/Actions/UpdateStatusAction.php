<?php

namespace App\Filament\Resources\RepairOrders\Actions;

use App\Filament\Resources\RepairOrders\RepairOrderStatus;
use App\Models\RepairOrder;
use App\Services\RepairOrderWorkflowService;
use Filament\Actions\Action;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Utilities\Get;

class UpdateStatusAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'updateStatus';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label('تحديث الحالة')
            ->icon('heroicon-o-arrow-path')
            ->color('primary')
            ->visible(fn (RepairOrder $record): bool => ! RepairOrderStatus::isTerminal($record->status)
                && (auth()->user()?->can('update', $record) ?? false))
            ->modalHeading('تحديث الحالة')
            ->modalSubmitActionLabel(fn (RepairOrder $record): string => match ($record->status) {
                'ready_for_delivery' => __('resources.repair_orders.actions.confirm_delivery.submit'),
                default => __('resources.repair_orders.actions.change_status.submit'),
            })
            ->form(fn (RepairOrder $record, RepairOrderWorkflowService $workflow): array => match ($record->status) {
                'ready_for_delivery' => $this->deliveryForm($record),
                default => $this->transitionForm($record, $workflow),
            })
            ->action(function (RepairOrder $record, Action $action, array $data, RepairOrderWorkflowService $workflow): void {
                abort_unless(auth()->user()?->can('update', $record) ?? false, 403);

                match ($record->status) {
                    'ready_for_delivery' => $this->handleDelivery($record, $action, $data, $workflow),
                    default => $this->handleTransition($record, $action, $data, $workflow),
                };
            })
            ->successNotification(fn (RepairOrder $record): Notification => Notification::make()
                ->success()
                ->title(match ($record->status) {
                    'delivered' => __('resources.repair_orders.actions.confirm_delivery.success'),
                    'beyond_repair' => __('resources.repair_orders.actions.mark_beyond_repair.success'),
                    default => __('resources.repair_orders.actions.change_status.success'),
                }));
    }

    /**
     * @return array<int, mixed>
     */
    protected function deliveryForm(RepairOrder $record): array
    {
        $invoice = $record->invoice;

        if ($invoice !== null) {
            $total = (float) $invoice->total;
            $paid = (float) $invoice->paid_total;
            $remaining = (float) $invoice->remaining_total;
        } else {
            $total = (float) $record->total_cost;
            $paid = 0.0;
            $remaining = $total;
        }

        $components = [
            Placeholder::make('payment_summary')
                ->label('حالة الدفع')
                ->content(sprintf(
                    'الإجمالي: %s ج.م — المدفوع: %s ج.م — المتبقي: %s ج.م',
                    number_format($total, 2),
                    number_format($paid, 2),
                    number_format($remaining, 2),
                ))
                ->columnSpanFull(),
        ];

        if ($remaining > 0) {
            $components[] = Placeholder::make('remaining_warning')
                ->label('تنبيه')
                ->content(sprintf(
                    'يوجد مبلغ متبقي قدره %s ج.م. سيتم تسجيل التسليم مع بقاء المبلغ مستحقًا على العميل.',
                    number_format($remaining, 2),
                ))
                ->columnSpanFull();
        }

        $components[] = TextInput::make('confirmed_by')
            ->label(__('resources.repair_orders.actions.confirm_delivery.confirmed_by'))
            ->placeholder(__('resources.repair_orders.actions.confirm_delivery.confirmed_by_placeholder'))
            ->maxLength(255);

        return $components;
    }

    /**
     * @return array<int, mixed>
     */
    protected function transitionForm(RepairOrder $record, RepairOrderWorkflowService $workflow): array
    {
        $options = collect(RepairOrderStatus::all())
            // rejected / delivered can never be picked from here.
            ->reject(fn (string $status): bool => $status === 'delivered')
            ->filter(fn (string $status): bool => $workflow->canTransitionTo($record, $status))
            ->mapWithKeys(fn (string $status): array => [$status => $status === 'cancelled'
                ? 'إلغاء الطلب'
                : RepairOrderStatus::label($status)])
            ->all();

        return [
            Select::make('status')
                ->label(__('resources.repair_orders.actions.change_status.status'))
                ->helperText(fn (): string => match ($record->status) {
                    'received' => __('resources.repair_orders.actions.change_status.hints.received'),
                    'in_repair' => __('resources.repair_orders.actions.change_status.hints.in_repair'),
                    default => '',
                })
                ->options($options)
                ->default(array_key_first($options))
                ->required()
                ->live()
                ->native(false),
            Textarea::make('reason')
                ->label(__('resources.repair_orders.actions.mark_beyond_repair.reason'))
                ->placeholder(__('resources.repair_orders.actions.mark_beyond_repair.reason_placeholder'))
                ->visible(fn (Get $get): bool => $get('status') === 'beyond_repair')
                ->required(fn (Get $get): bool => $get('status') === 'beyond_repair')
                ->maxLength(1000)
                ->rows(3)
                ->columnSpanFull(),
        ];
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleDelivery(RepairOrder $record, Action $action, array $data, RepairOrderWorkflowService $workflow): void
    {
        if (! $workflow->canTransitionTo($record, 'delivered')) {
            $this->deny($action, __('resources.repair_orders.actions.confirm_delivery.not_allowed'));
        }

        $workflow->confirmDelivery($record, $data['confirmed_by'] ?: null);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    protected function handleTransition(RepairOrder $record, Action $action, array $data, RepairOrderWorkflowService $workflow): void
    {
        $target = $data['status'];

        if (! $workflow->canTransitionTo($record, $target)) {
            $this->deny($action, __('resources.repair_orders.actions.change_status.not_allowed'));
        }

        try {
            match ($target) {
                'beyond_repair' => $workflow->markBeyondRepair($record, $data['reason'] ?? ''),
                'cancelled' => $workflow->cancel($record),
                default => $workflow->transitionTo($record, $target),
            };
        } catch (\Throwable $e) {
            $this->deny($action, __('resources.repair_orders.actions.change_status.not_allowed'), $e->getMessage());
        }
    }

    protected function deny(Action $action, string $title, ?string $body = null): void
    {
        $notification = Notification::make()->danger()->title($title);

        if ($body !== null) {
            $notification->body($body);
        }

        $notification->send();

        $action->halt();
    }
}
