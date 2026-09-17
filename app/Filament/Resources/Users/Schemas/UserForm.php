<?php

namespace App\Filament\Resources\Users\Schemas;

use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Support\Facades\Hash;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('resources.users.form.section'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('resources.users.form.name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('email')
                            ->label(__('resources.users.form.email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        DateTimePicker::make('email_verified_at')
                            ->label(__('resources.users.form.email_verified_at')),
                        TextInput::make('password')
                            ->label(__('resources.users.form.password'))
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(fn (?string $state): bool => filled($state))
                            ->dehydrateStateUsing(fn (string $state): string => Hash::make($state))
                            ->maxLength(255),
                        TextInput::make('password_confirmation')
                            ->label(__('resources.users.form.password_confirmation'))
                            ->password()
                            ->revealable()
                            ->required(fn (string $operation): bool => $operation === 'create')
                            ->dehydrated(false)
                            ->same('password'),
                        Select::make('roles')
                            ->label(__('resources.users.form.roles'))
                            ->relationship('roles', 'name')
                            ->multiple()
                            ->searchable()
                            ->preload()
                            ->required()
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
