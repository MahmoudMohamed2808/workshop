<?php

namespace App\Filament\Resources\RepairOrders\Actions;

use App\Filament\Resources\RepairOrders\RepairOrderStatus;
use App\Models\RepairOrder;
use App\Services\RepairOrderWorkflowService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Notifications\Notification;

class ChangeStatusAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'changeStatus';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (): string => __('resources.repair_orders.actions.change_status.label'))
            ->icon('heroicon-o-arrow-path')
            ->color('gray')
            ->visible(fn (RepairOrder $record): bool => ! RepairOrderStatus::isTerminal($record->status)
                && (auth()->user()?->can('update', $record) ?? false))
            ->modalHeading(fn (): string => __('resources.repair_orders.actions.change_status.heading'))
            ->modalSubmitActionLabel(fn (): string => __('resources.repair_orders.actions.change_status.submit'))
            ->disabled(fn (RepairOrder $record): bool => RepairOrderStatus::isTerminal($record->status))
            ->form(fn (RepairOrder $record, RepairOrderWorkflowService $workflow): array => [
                Select::make('status')
                    ->label(fn (): string => __('resources.repair_orders.actions.change_status.status'))
                    ->helperText(fn (): string => match ($record->status) {
                        'received' => __('resources.repair_orders.actions.change_status.hints.received'),
                        'in_repair' => __('resources.repair_orders.actions.change_status.hints.in_repair'),
                        default => '',
                    })
                    ->options(collect(RepairOrderStatus::all())
                        // الحالات التي تحتاج سبب/تأكيد لها أزرار مخصصة — نخفيها هنا لمنع تخطي البيانات.
                        ->reject(fn (string $status): bool => in_array($status, ['beyond_repair', 'delivered'], true))
                        ->filter(fn (string $status): bool => $workflow->canTransitionTo($record, $status))
                        ->mapWithKeys(fn (string $status): array => [$status => RepairOrderStatus::label($status)])
                        ->all())
                    ->required()
                    ->native(false),
            ])
            ->action(function (RepairOrder $record, Action $action, array $data, RepairOrderWorkflowService $workflow): void {
                abort_unless(auth()->user()?->can('update', $record) ?? false, 403);

                if (! $workflow->canTransitionTo($record, $data['status'])) {
                    Notification::make()
                        ->danger()
                        ->title(__('resources.repair_orders.actions.change_status.not_allowed'))
                        ->send();

                    $action->halt();

                    return;
                }

                try {
                    // الحفاظ على التواريخ عند استخدام التغيير العام.
                    match ($data['status']) {
                        'cancelled' => $workflow->cancel($record),
                        default => $workflow->transitionTo($record, $data['status']),
                    };
                } catch (\Throwable $e) {
                    Notification::make()
                        ->danger()
                        ->title(__('resources.repair_orders.actions.change_status.not_allowed'))
                        ->body($e->getMessage())
                        ->send();

                    $action->halt();
                }
            })
            ->successNotification(fn (): Notification => Notification::make()
                ->success()
                ->title(__('resources.repair_orders.actions.change_status.success')));
    }
}
