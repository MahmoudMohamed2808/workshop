<?php

namespace App\Filament\Resources\Problems;

use App\Filament\Resources\Problems\Pages\CreateProblem;
use App\Filament\Resources\Problems\Pages\EditProblem;
use App\Filament\Resources\Problems\Pages\ListProblems;
use App\Filament\Resources\Problems\Schemas\ProblemForm;
use App\Filament\Resources\Problems\Tables\ProblemsTable;
use App\Models\Problem;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class ProblemResource extends Resource
{
    protected static ?string $model = Problem::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('resources.problems.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.problems.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('resources.problems.navigation');
    }

    public static function form(Schema $schema): Schema
    {
        return ProblemForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return ProblemsTable::configure($table);
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
            'index' => ListProblems::route('/'),
            'create' => CreateProblem::route('/create'),
            'edit' => EditProblem::route('/{record}/edit'),
        ];
    }
}
