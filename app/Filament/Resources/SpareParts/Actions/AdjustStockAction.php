<?php

namespace App\Filament\Resources\SpareParts\Actions;

use App\Models\SparePart;
use App\Services\InventoryService;
use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use InvalidArgumentException;

class AdjustStockAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'adjustStock';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (): string => __('resources.spare_parts.actions.adjust_stock.label'))
            ->icon('heroicon-o-archive-box-arrow-down')
            ->color('warning')
            // تعديل المخزون للإدارة والموظف فقط (صلاحية التعديل) — الفني عرض فقط.
            ->visible(fn (SparePart $record): bool => auth()->user()?->can('update', $record) ?? false)
            ->modalHeading(fn (SparePart $record): string => __('resources.spare_parts.actions.adjust_stock.heading', ['name' => $record->name]))
            ->modalSubmitActionLabel(fn (): string => __('resources.spare_parts.actions.adjust_stock.submit'))
            ->form(fn (): array => [
                Select::make('type')
                    ->label(fn (): string => __('resources.spare_parts.actions.adjust_stock.type'))
                    ->options([
                        'increase' => __('resources.spare_parts.actions.adjust_stock.type_increase'),
                        'decrease' => __('resources.spare_parts.actions.adjust_stock.type_decrease'),
                    ])
                    ->default('increase')
                    ->required()
                    ->native(false),
                TextInput::make('quantity')
                    ->label(fn (): string => __('resources.spare_parts.actions.adjust_stock.quantity'))
                    ->numeric()
                    ->integer()
                    ->minValue(1)
                    ->required(),
                Textarea::make('reason')
                    ->label(fn (): string => __('resources.spare_parts.actions.adjust_stock.reason'))
                    ->placeholder(fn (): string => __('resources.spare_parts.actions.adjust_stock.reason_placeholder'))
                    ->required()
                    ->maxLength(1000)
                    ->rows(3)
                    ->columnSpanFull(),
            ])
            ->action(function (SparePart $record, Action $action, array $data, InventoryService $inventory): void {
                abort_unless(auth()->user()?->can('update', $record) ?? false, 403);

                $delta = $data['type'] === 'decrease' ? -(int) $data['quantity'] : (int) $data['quantity'];

                try {
                    $inventory->adjustStock($record, $delta, $data['reason']);
                } catch (InvalidArgumentException) {
                    Notification::make()
                        ->danger()
                        ->title(__('resources.spare_parts.actions.adjust_stock.insufficient_stock'))
                        ->send();

                    $action->halt();

                    return;
                }
            })
            ->successNotification(fn (): Notification => Notification::make()
                ->success()
                ->title(__('resources.spare_parts.actions.adjust_stock.success')));
    }
}
