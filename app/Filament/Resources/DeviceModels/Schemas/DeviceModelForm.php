<?php

namespace App\Filament\Resources\DeviceModels\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class DeviceModelForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('resources.device_models.form.section'))
                    ->schema([
                        Select::make('brand_id')
                            ->label(__('resources.device_models.form.brand'))
                            ->placeholder(__('resources.device_models.form.brand_placeholder'))
                            ->relationship('brand', 'name')
                            ->searchable()
                            ->preload()
                            ->required(),
                        TextInput::make('name')
                            ->label(__('resources.device_models.form.name'))
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),
            ]);
    }
}
