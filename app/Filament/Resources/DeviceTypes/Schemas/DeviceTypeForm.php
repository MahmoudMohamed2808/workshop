<?php

namespace App\Filament\Resources\DeviceTypes\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Support\Str;

class DeviceTypeForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('resources.device_types.form.section'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('resources.device_types.form.name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (string $operation, ?string $state, Set $set): mixed => $operation === 'create'
                                ? $set('slug', Str::slug((string) $state))
                                : null),
                        TextInput::make('slug')
                            ->label(__('resources.device_types.form.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        Toggle::make('is_active')
                            ->label(__('resources.device_types.form.is_active'))
                            ->default(true),
                    ])
                    ->columns(2),
            ]);
    }
}
