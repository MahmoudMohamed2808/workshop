<?php

namespace App\Filament\Resources\Problems\Tables;

use App\Filament\Actions\ToggleActiveAction;
use App\Models\Problem;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class ProblemsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('resources.problems.table.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('deviceType.name')
                    ->label(__('resources.problems.table.device_type'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('brand.name')
                    ->label(__('resources.problems.table.brand'))
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('deviceModel.name')
                    ->label(__('resources.problems.table.model'))
                    ->searchable()
                    ->placeholder('—'),
                TextColumn::make('usage_count')
                    ->label(__('resources.problems.table.usage_count'))
                    ->sortable(),
                TextColumn::make('default_cost_range')
                    ->label(__('resources.problems.table.cost_range')),
                TextColumn::make('tags_list')
                    ->label(__('resources.problems.table.tags'))
                    ->getStateUsing(fn (Problem $record): string => $record->tags->pluck('name')->join(', '))
                    ->toggleable(isToggledHiddenByDefault: true),
                IconColumn::make('is_active')
                    ->label(__('resources.problems.table.is_active'))
                    ->boolean()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('device_type')
                    ->label(__('resources.problems.table.device_type'))
                    ->relationship('deviceType', 'name')
                    ->searchable()
                    ->preload(),
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
            ->defaultSort('usage_count', 'desc')
            ->emptyStateHeading(__('resources.problems.table.empty_heading'))
            ->emptyStateDescription(__('resources.problems.table.empty_description'));
    }
}
