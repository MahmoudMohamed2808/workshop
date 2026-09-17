<?php

namespace App\Filament\Resources\Technicians\Pages;

use App\Filament\Resources\Technicians\TechnicianResource;
use Filament\Resources\Pages\CreateRecord;

class CreateTechnician extends CreateRecord
{
    protected static string $resource = TechnicianResource::class;

    protected function afterCreate(): void
    {
        $record = $this->record;

        if (! $record->hasRole(TechnicianResource::TECHNICIAN_ROLE)) {
            $record->assignRole(TechnicianResource::TECHNICIAN_ROLE);
        }
    }
}
