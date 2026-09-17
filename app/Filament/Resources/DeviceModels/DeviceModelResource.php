<?php

namespace App\Filament\Resources\DeviceModels;

use App\Filament\Resources\DeviceModels\Pages\CreateDeviceModel;
use App\Filament\Resources\DeviceModels\Pages\EditDeviceModel;
use App\Filament\Resources\DeviceModels\Pages\ListDeviceModels;
use App\Filament\Resources\DeviceModels\RelationManagers\ProblemsRelationManager;
use App\Filament\Resources\DeviceModels\Schemas\DeviceModelForm;
use App\Filament\Resources\DeviceModels\Tables\DeviceModelsTable;
use App\Models\DeviceModel;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class DeviceModelResource extends Resource
{
    protected static ?string $model = DeviceModel::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('resources.device_models.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.device_models.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('resources.device_models.navigation');
    }

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    public static function form(Schema $schema): Schema
    {
        return DeviceModelForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return DeviceModelsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            ProblemsRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListDeviceModels::route('/'),
            'create' => CreateDeviceModel::route('/create'),
            'edit' => EditDeviceModel::route('/{record}/edit'),
        ];
    }
}
