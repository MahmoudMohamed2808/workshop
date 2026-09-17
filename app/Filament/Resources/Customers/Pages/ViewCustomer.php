<?php

namespace App\Filament\Resources\Customers\Pages;

use App\Filament\Resources\Customers\Actions\SendWhatsappMessageAction;
use App\Filament\Resources\Customers\CustomerResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCustomer extends ViewRecord
{
    protected static string $resource = CustomerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            SendWhatsappMessageAction::make(),
            EditAction::make(),
        ];
    }
}
