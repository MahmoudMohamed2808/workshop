<?php

namespace App\Filament\Resources\Technicians;

use App\Filament\Resources\Technicians\Pages\CreateTechnician;
use App\Filament\Resources\Technicians\Pages\EditTechnician;
use App\Filament\Resources\Technicians\Pages\ListTechnicians;
use App\Filament\Resources\Technicians\Schemas\TechnicianForm;
use App\Filament\Resources\Technicians\Tables\TechniciansTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class TechnicianResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?string $recordTitleAttribute = 'name';

    public const TECHNICIAN_ROLE = User::ROLE_TECHNICIAN;

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->whereHas('roles', fn (Builder $query): Builder => $query->where('name', self::TECHNICIAN_ROLE));
    }

    public static function getModelLabel(): string
    {
        return __('resources.technicians.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.technicians.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('resources.technicians.navigation');
    }

    public static function form(Schema $schema): Schema
    {
        return TechnicianForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return TechniciansTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListTechnicians::route('/'),
            'create' => CreateTechnician::route('/create'),
            'edit' => EditTechnician::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'email'];
    }
}
