<?php

namespace App\Filament\Resources\Customers\Schemas;

use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class CustomerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('resources.customers.form.section'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('resources.customers.form.name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('phone')
                            ->label(__('resources.customers.form.phone'))
                            ->placeholder(__('resources.customers.form.phone_placeholder'))
                            ->tel()
                            ->required()
                            ->maxLength(20)
                            ->unique(ignoreRecord: true),
                        // TextInput::make('whatsapp_phone')
                        //     ->label(__('resources.customers.form.whatsapp_phone'))
                        //     ->tel()
                        //     ->maxLength(20),
                        Textarea::make('address')
                            ->label(__('resources.customers.form.address'))
                            ->rows(3)
                            ->columnSpanFull(),
                        Textarea::make('notes')
                            ->label(__('resources.customers.form.notes'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
            ]);
    }
}
