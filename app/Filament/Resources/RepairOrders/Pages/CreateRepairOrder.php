<?php

namespace App\Filament\Resources\RepairOrders\Pages;

use App\Filament\Resources\RepairOrders\RepairOrderResource;
use App\Services\CostCalculatorService;
use Filament\Resources\Pages\CreateRecord;

class CreateRepairOrder extends CreateRecord
{
    protected static string $resource = RepairOrderResource::class;

    /**
     * @param  array<string, mixed>  $data
     * @return array<string, mixed>
     */
    protected function mutateFormDataBeforeCreate(array $data): array
    {
        $data['order_number'] ??= 'pending';
        $data['created_by'] ??= auth()->id();
        $data['status'] ??= 'received';

        return $data;
    }

    protected function afterCreate(): void
    {
        if ($this->record->order_number === 'pending' || blank($this->record->order_number)) {
            $this->record->updateQuietly([
                'order_number' => sprintf('RO-%s-%06d', $this->record->created_at?->format('Y') ?? now()->format('Y'), $this->record->id),
            ]);
        }

        app(CostCalculatorService::class)->recalculateOrderTotal($this->record->refresh());
    }
}
