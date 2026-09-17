<?php

namespace App\Filament\Resources\Problems\Pages;

use App\Filament\Actions\ToggleActiveAction;
use App\Filament\Resources\Problems\ProblemResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditProblem extends EditRecord
{
    protected static string $resource = ProblemResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ToggleActiveAction::make(),
            DeleteAction::make(),
        ];
    }
}
