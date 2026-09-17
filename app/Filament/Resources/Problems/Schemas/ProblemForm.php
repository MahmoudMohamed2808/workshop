<?php

namespace App\Filament\Resources\Problems\Schemas;

use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class ProblemForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('resources.problems.form.section'))
                    ->schema([
                        Select::make('device_type_id')
                            ->label(__('resources.problems.form.device_type'))
                            ->placeholder(__('resources.problems.form.device_type_placeholder'))
                            ->relationship('deviceType', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('brand_id', null);
                                $set('device_model_id', null);
                            }),
                        Select::make('brand_id')
                            ->label(__('resources.problems.form.brand'))
                            ->placeholder(__('resources.problems.form.brand_placeholder'))
                            ->relationship('brand', 'name', modifyQueryUsing: fn (Builder $query, Get $get) => $query
                                ->when($get('device_type_id'), fn (Builder $q, $typeId): Builder => $q->where('device_type_id', $typeId)))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('device_model_id', null)),
                        Select::make('device_model_id')
                            ->label(__('resources.problems.form.model'))
                            ->placeholder(__('resources.problems.form.model_placeholder'))
                            ->relationship('deviceModel', 'name', modifyQueryUsing: fn (Builder $query, Get $get) => $query
                                ->when($get('brand_id'), fn (Builder $q, $brandId): Builder => $q->where('brand_id', $brandId)))
                            ->searchable()
                            ->preload()
                            ->disabled(fn (Get $get): bool => blank($get('brand_id'))),
                        TextInput::make('name')
                            ->label(__('resources.problems.form.name'))
                            ->required()
                            ->maxLength(255),
                        Textarea::make('description')
                            ->label(__('resources.problems.form.description'))
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('default_cost_min')
                            ->label(__('resources.problems.form.cost_min'))
                            ->numeric()
                            ->minValue(0),
                        TextInput::make('default_cost_max')
                            ->label(__('resources.problems.form.cost_max'))
                            ->numeric()
                            ->minValue(0),
                        Select::make('tags')
                            ->label(__('resources.problems.form.tags'))
                            ->placeholder(__('resources.problems.form.tags_placeholder'))
                            ->relationship('tags', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label(__('resources.problems.form.tags'))
                                    ->required()
                                    ->maxLength(255),
                            ])
                            ->columnSpanFull(),
                        Toggle::make('is_active')
                            ->label(__('resources.problems.form.is_active'))
                            ->default(true),
                    ])
                    ->columns(2),
                Section::make(__('resources.problems.form.bundle_section'))
                    ->description(__('resources.problems.form.bundle_help'))
                    ->schema([
                        Repeater::make('suggested_accessories')
                            ->label(__('resources.problems.form.suggested_accessories'))
                            ->simple(TextInput::make('item')
                                ->label(__('resources.problems.form.accessory_item'))
                                ->maxLength(255))
                            ->columnSpanFull(),
                        Select::make('suggested_technician_id')
                            ->label(__('resources.problems.form.suggested_technician'))
                            ->placeholder(__('resources.problems.form.suggested_technician_placeholder'))
                            ->relationship('suggestedTechnician', 'name')
                            ->searchable()
                            ->preload(),
                        Select::make('suggestedParts')
                            ->label(__('resources.problems.form.suggested_parts'))
                            ->relationship('suggestedParts', 'name')
                            ->multiple()
                            ->searchable(['name', 'code'])
                            ->preload(),
                    ])
                    ->columns(2),
            ]);
    }
}
