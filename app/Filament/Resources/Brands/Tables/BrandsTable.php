<?php

namespace App\Filament\Resources\Brands\Tables;

use App\Filament\Actions\ToggleActiveAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class BrandsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('resources.brands.table.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('deviceType.name')
                    ->label(__('resources.brands.table.device_type'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('device_models_count')
                    ->label(__('resources.brands.table.models_count'))
                    ->counts('deviceModels')
                    ->sortable(),
                IconColumn::make('is_active')
                    ->label(__('resources.brands.table.is_active'))
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
            ->emptyStateHeading(__('resources.brands.table.empty_heading'))
            ->emptyStateDescription(__('resources.brands.table.empty_description'));
    }
}
