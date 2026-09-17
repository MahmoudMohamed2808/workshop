<?php

namespace App\Filament\Resources\Brands\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class BrandForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('resources.brands.form.section'))
                    ->schema([
                        Select::make('device_type_id')
                            ->label(__('resources.brands.form.device_type'))
                            ->placeholder(__('resources.brands.form.device_type_placeholder'))
                            ->relationship('deviceType', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label(__('resources.brands.form.name'))
                            ->required()
                            ->maxLength(255),
                        Toggle::make('is_active')
                            ->label(__('resources.brands.form.is_active'))
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
