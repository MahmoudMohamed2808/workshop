<?php

namespace App\Filament\Resources\RepairOrders\Pages;

use App\Filament\Resources\RepairOrders\Actions\InvoiceAction;
use App\Filament\Resources\RepairOrders\Actions\SendWhatsappUpdateAction;
use App\Filament\Resources\RepairOrders\Actions\UpdateStatusAction;
use App\Filament\Resources\RepairOrders\RepairOrderResource;
use App\Filament\Resources\RepairOrders\Widgets\RepairOrderPaymentSummary;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;
use Filament\Widgets\Widget;

class ViewRepairOrder extends ViewRecord
{
    protected static string $resource = RepairOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            UpdateStatusAction::make(),
            InvoiceAction::make(),
            SendWhatsappUpdateAction::make(),
            EditAction::make(),
        ];
    }

    /**
     * @return array<class-string<Widget>>
     */
    protected function getHeaderWidgets(): array
    {
        return [
            RepairOrderPaymentSummary::class,
        ];
    }
}
