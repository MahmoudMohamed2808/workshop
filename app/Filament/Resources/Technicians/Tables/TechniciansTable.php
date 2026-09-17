<?php

namespace App\Filament\Resources\Technicians\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class TechniciansTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('resources.technicians.table.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('email')
                    ->label(__('resources.technicians.table.email'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('repair_orders_count')
                    ->label(__('resources.technicians.table.orders_count'))
                    ->counts('repairOrders')
                    ->badge()
                    ->sortable(),
                IconColumn::make('email_verified_at')
                    ->label(__('resources.technicians.table.verified'))
                    ->boolean()
                    ->getStateUsing(fn ($record): bool => $record->email_verified_at !== null)
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('resources.technicians.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('resources.technicians.table.empty_heading'))
            ->emptyStateDescription(__('resources.technicians.table.empty_description'));
    }
}
