<?php

namespace App\Filament\Resources\DeviceModels\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class DeviceModelsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('resources.device_models.table.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('brand.name')
                    ->label(__('resources.device_models.table.brand'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('brand.deviceType.name')
                    ->label(__('resources.device_models.table.device_type'))
                    ->searchable()
                    ->sortable(),
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
            ->emptyStateHeading(__('resources.device_models.table.empty_heading'))
            ->emptyStateDescription(__('resources.device_models.table.empty_description'));
    }
}
