<?php

namespace App\Filament\Resources\DeviceTypes\RelationManagers;

use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class BrandsRelationManager extends RelationManager
{
    protected static string $relationship = 'brands';

    protected static ?string $recordTitleAttribute = 'name';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('resources.device_types.relations.brands.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->label(__('resources.device_types.relations.brands.name'))
                    ->required()
                    ->maxLength(255),
                Toggle::make('is_active')
                    ->label(__('resources.device_types.relations.brands.is_active'))
                    ->default(true),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('resources.device_types.relations.brands.title'))
            ->recordTitleAttribute('name')
            ->columns([
                TextColumn::make('name')
                    ->label(__('resources.device_types.relations.brands.name'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('device_models_count')
                    ->label(__('resources.device_types.relations.brands.models_count'))
                    ->counts('deviceModels'),
                IconColumn::make('is_active')
                    ->label(__('resources.device_types.relations.brands.is_active'))
                    ->boolean(),
            ])
            ->headerActions([
                CreateAction::make(),
            ])
            ->recordActions([
                EditAction::make(),
                DeleteAction::make(),
            ]);
    }
}
