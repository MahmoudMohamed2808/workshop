<?php

namespace App\Filament\Resources\SpareParts\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class SparePartForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('resources.spare_parts.form.section'))
                    ->schema([
                        TextInput::make('code')
                            ->label(__('resources.spare_parts.form.code'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),
                        TextInput::make('name')
                            ->label(__('resources.spare_parts.form.name'))
                            ->required()
                            ->maxLength(255),
                        TextInput::make('purchase_price')
                            ->label(__('resources.spare_parts.form.purchase_price'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('selling_price')
                            ->label(__('resources.spare_parts.form.selling_price'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0),
                        TextInput::make('quantity')
                            ->label(__('resources.spare_parts.form.quantity'))
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(0)
                            ->disabledOn('edit')
                            ->helperText(__('resources.spare_parts.form.quantity_edit_help')),
                        TextInput::make('minimum_stock')
                            ->label(__('resources.spare_parts.form.minimum_stock'))
                            ->numeric()
                            ->integer()
                            ->minValue(0)
                            ->default(0),
                    ])
                    ->columns(2),
            ]);
    }
}
