<?php

namespace App\Filament\Resources\DeviceTypes\Tables;

use App\Filament\Actions\ToggleActiveAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class DeviceTypesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('resources.device_types.table.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('slug')
                    ->label(__('resources.device_types.table.slug'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('brands_count')
                    ->label(__('resources.device_types.table.brands_count'))
                    ->counts('brands')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('resources.device_types.table.is_active'))
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('resources.common.filters.is_active'))
                    ->placeholder(__('resources.common.filters.all'))
                    ->trueLabel(__('resources.common.filters.active'))
                    ->falseLabel(__('resources.common.filters.inactive')),
            ])
            ->recordActions([
                EditAction::make(),
                ToggleActiveAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('resources.device_types.table.empty_heading'))
            ->emptyStateDescription(__('resources.device_types.table.empty_description'));
    }
}
