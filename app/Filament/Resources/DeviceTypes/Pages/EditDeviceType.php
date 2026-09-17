<?php

namespace App\Filament\Resources\DeviceTypes\Pages;

use App\Filament\Actions\ToggleActiveAction;
use App\Filament\Resources\DeviceTypes\DeviceTypeResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditDeviceType extends EditRecord
{
    protected static string $resource = DeviceTypeResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ToggleActiveAction::make(),
            DeleteAction::make(),
        ];
    }
}
