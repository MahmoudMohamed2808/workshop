<?php

namespace App\Filament\Resources\RepairOrders\Pages;

use App\Filament\Resources\RepairOrders\Actions\ChangeStatusAction;
use App\Filament\Resources\RepairOrders\Actions\PrintSaleInvoiceAction;
use App\Filament\Resources\RepairOrders\RepairOrderResource;
use App\Services\CostCalculatorService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ForceDeleteAction;
use Filament\Actions\RestoreAction;
use Filament\Resources\Pages\EditRecord;

class EditRepairOrder extends EditRecord
{
    protected static string $resource = RepairOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ChangeStatusAction::make(),
            PrintSaleInvoiceAction::make(),
            DeleteAction::make(),
            ForceDeleteAction::make(),
            RestoreAction::make(),
        ];
    }

    protected function afterSave(): void
    {
        app(CostCalculatorService::class)->recalculateOrderTotal($this->record->refresh());
    }
}
