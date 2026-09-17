<?php

namespace App\Filament\Resources\Customers\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ForceDeleteBulkAction;
use Filament\Actions\RestoreBulkAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class CustomersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('resources.customers.table.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('phone')
                    ->label(__('resources.customers.table.phone'))
                    ->searchable()
                    ->copyable(),
                // TextColumn::make('whatsapp_phone')
                //     ->label(__('resources.customers.table.whatsapp_phone'))
                //     ->searchable()
                //     ->toggleable(),
                TextColumn::make('address')
                    ->label(__('resources.customers.table.address'))
                    ->searchable()
                    ->limit(30)
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('repair_orders_count')
                    ->label(__('resources.customers.table.orders_count'))
                    ->counts('repairOrders')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('resources.customers.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                    ForceDeleteBulkAction::make(),
                    RestoreBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('resources.customers.table.empty_heading'))
            ->emptyStateDescription(__('resources.customers.table.empty_description'));
    }
}
