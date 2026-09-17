<?php

namespace App\Filament\Resources\Technicians\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class TechnicianForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('resources.technicians.form.section'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('resources.technicians.form.name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('resources.technicians.form.email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        DateTimePicker::make('email_verified_at')
                            ->label(__('resources.technicians.form.email_verified_at')),
                        TextInput::make('password')
                            ->label(__('resources.technicians.form.password'))
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->maxLength(255),
                        TextInput::make('password_confirmation')
                            ->label(__('resources.technicians.form.password_confirmation'))
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(false)
                            ->same('password'),
                    ])
                    ->columns(2),
            ]);
    }
}
