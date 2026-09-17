<?php

namespace App\Filament\Resources\SpareParts\Tables;

use App\Filament\Resources\SpareParts\Actions\AdjustStockAction;
use App\Models\SparePart;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class SparePartsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label(__('resources.spare_parts.table.code'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),
                TextColumn::make('name')
                    ->label(__('resources.spare_parts.table.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('quantity')
                    ->label(__('resources.spare_parts.table.quantity'))
                    ->sortable()
                    ->badge()
                    ->color(fn (SparePart $record): string => $record->is_low_stock ? 'danger' : 'success'),
                TextColumn::make('minimum_stock')
                    ->label(__('resources.spare_parts.table.minimum_stock'))
                    ->sortable(),
                IconColumn::make('is_low_stock')
                    ->label(__('resources.spare_parts.table.low_stock'))
                    ->boolean()
                    ->sortable(),
                TextColumn::make('selling_price')
                    ->label(__('resources.spare_parts.table.selling_price'))
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('purchase_price')
                    ->label(__('resources.spare_parts.table.purchase_price'))
                    ->money('EGP')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label(__('resources.spare_parts.table.updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->recordActions([
                AdjustStockAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('name')
            ->emptyStateHeading(__('resources.spare_parts.table.empty_heading'))
            ->emptyStateDescription(__('resources.spare_parts.table.empty_description'));
    }
}
