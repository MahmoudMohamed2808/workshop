<?php

namespace App\Filament\Resources\SpareParts\Pages;

use App\Filament\Resources\SpareParts\Actions\AdjustStockAction;
use App\Filament\Resources\SpareParts\SparePartResource;
use Filament\Actions\DeleteAction;
use Filament\Resources\Pages\EditRecord;

class EditSparePart extends EditRecord
{
    protected static string $resource = SparePartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            AdjustStockAction::make(),
            DeleteAction::make(),
        ];
    }
}
