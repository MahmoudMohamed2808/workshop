<?php

namespace App\Filament\Resources\Technicians\Pages;

use App\Filament\Resources\Technicians\TechnicianResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditTechnician extends EditRecord
{
    protected static string $resource = TechnicianResource::class;

    protected function getHeaderActions(): array
    {
        return [
            DeleteAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        $record = $this->record;

        if (! $record->hasRole(TechnicianResource::TECHNICIAN_ROLE)) {
            $record->assignRole(TechnicianResource::TECHNICIAN_ROLE);
        }
    }
}
