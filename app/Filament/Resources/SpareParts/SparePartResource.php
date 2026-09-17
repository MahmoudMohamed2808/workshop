<?php

namespace App\Filament\Resources\SpareParts;

use App\Filament\Resources\SpareParts\Pages\CreateSparePart;
use App\Filament\Resources\SpareParts\Pages\EditSparePart;
use App\Filament\Resources\SpareParts\Pages\ListSpareParts;
use App\Filament\Resources\SpareParts\RelationManagers\UsageHistoryRelationManager;
use App\Filament\Resources\SpareParts\Schemas\SparePartForm;
use App\Filament\Resources\SpareParts\Tables\SparePartsTable;
use App\Models\SparePart;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SparePartResource extends Resource
{
    protected static ?string $model = SparePart::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('resources.spare_parts.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.spare_parts.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('resources.spare_parts.navigation');
    }

    public static function form(Schema $schema): Schema
    {
        return SparePartForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SparePartsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            UsageHistoryRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSpareParts::route('/'),
            'create' => CreateSparePart::route('/create'),
            'edit' => EditSparePart::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['code', 'name'];
    }
}
