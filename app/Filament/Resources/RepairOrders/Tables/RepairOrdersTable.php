<?php

namespace App\Filament\Resources\RepairOrders\Tables;

use App\Filament\Resources\RepairOrders\Actions\InvoiceAction;
use App\Filament\Resources\RepairOrders\Actions\UpdateStatusAction;
use App\Filament\Resources\RepairOrders\PaymentStatus;
use App\Filament\Resources\RepairOrders\RepairOrderStatus;
use App\Models\RepairOrder;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class RepairOrdersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('order_number')
                    ->label(__('resources.repair_orders.table.order_number'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('customer.name')
                    ->label(__('resources.repair_orders.table.customer'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('deviceType.name')
                    ->label(__('resources.repair_orders.table.device'))
                    ->formatStateUsing(fn ($state, $record): string => trim(implode(' · ', array_filter([
                        $record->deviceType?->name,
                        $record->brand?->name,
                        $record->deviceModel?->name,
                    ])) ?: '—'))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('resources.repair_orders.table.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => RepairOrderStatus::label($state))
                    ->color(fn (string $state): string => RepairOrderStatus::color($state)),
                TextColumn::make('payment_status')
                    ->label(__('resources.repair_orders.table.payment_status'))
                    ->badge()
                    ->getStateUsing(fn (RepairOrder $record): string => $record->payment_status)
                    ->formatStateUsing(fn (string $state): string => PaymentStatus::label($state))
                    ->color(fn (string $state): string => PaymentStatus::color($state)),
                TextColumn::make('invoice.remaining_total')
                    ->label(__('resources.repair_orders.table.remaining'))
                    ->money('EGP')
                    ->placeholder('—')
                    ->sortable(),
                TextColumn::make('total_cost')
                    ->label(__('resources.repair_orders.table.total_cost'))
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('technician.name')
                    ->label(__('resources.repair_orders.table.technician'))
                    ->placeholder('—')
                    ->searchable(),
                TextColumn::make('created_at')
                    ->label(__('resources.repair_orders.table.created_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label(__('resources.repair_orders.filters.status'))
                    ->options(RepairOrderStatus::options()),
                SelectFilter::make('payment_status')
                    ->label(__('resources.repair_orders.filters.payment_status'))
                    ->options(PaymentStatus::options())
                    ->query(function (Builder $query, array $data): Builder {
                        $value = $data['value'] ?? null;

                        if (filled($value)) {
                            $query->wherePaymentStatus($value);
                        }

                        return $query;
                    }),
                SelectFilter::make('technician_id')
                    ->label(__('resources.repair_orders.filters.technician'))
                    ->relationship('technician', 'name')
                    ->searchable()
                    ->preload(),
                SelectFilter::make('device_type_id')
                    ->label(__('resources.repair_orders.filters.device_type'))
                    ->relationship('deviceType', 'name')
                    ->searchable()
                    ->preload(),
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                UpdateStatusAction::make(),
                InvoiceAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading(__('resources.repair_orders.table.empty_heading'))
            ->emptyStateDescription(__('resources.repair_orders.table.empty_description'));
    }

    /**
     * @param  Builder<RepairOrder>  $query
     */
    public static function modifyQuery(Builder $query): Builder
    {
        return $query->with(['customer', 'deviceType', 'brand', 'deviceModel', 'technician', 'invoice']);
    }
}
