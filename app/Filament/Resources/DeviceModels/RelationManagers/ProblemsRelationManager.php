<?php

namespace App\Filament\Resources\DeviceModels\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class ProblemsRelationManager extends RelationManager
{
    protected static string $relationship = 'problems';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('resources.device_models.relations.problems.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('resources.device_models.relations.problems.title'))
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('resources.problems.columns.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('usage_count')
                    ->label(__('resources.problems.columns.usage_count'))
                    ->sortable(),
                TextColumn::make('default_cost_range')
                    ->label(__('resources.problems.columns.cost_range')),
                IconColumn::make('is_active')
                    ->label(__('resources.problems.columns.is_active'))
                    ->boolean(),
            ])
            ->filters([
                TernaryFilter::make('is_active')
                    ->label(__('resources.common.filters.is_active'))
                    ->placeholder(__('resources.common.filters.all'))
                    ->trueLabel(__('resources.common.filters.active'))
                    ->falseLabel(__('resources.common.filters.inactive')),
            ])
            ->defaultSort('usage_count', 'desc');
    }
}
