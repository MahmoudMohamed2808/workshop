<?php

namespace App\Filament\Resources\RepairOrders\Pages;

use App\Filament\Resources\RepairOrders\RepairOrderResource;
use App\Models\RepairOrder;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListRepairOrders extends ListRecords
{
    protected static string $resource = RepairOrderResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    /**
     * تبويبات سريعة للحالة: تسهل الوصول لمرحلة الشغل الحالية.
     *
     * @return array<string, Tab>
     */
    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('resources.repair_orders.tabs.all'))
                ->badge(RepairOrder::query()->count()),
            'needs_action' => Tab::make(__('resources.repair_orders.tabs.needs_action'))
                ->query(fn (Builder $query): Builder => $query->whereIn('status', ['received']))
                ->badge(RepairOrder::query()->whereIn('status', ['received'])->count())
                ->badgeColor('warning'),
            'in_progress' => Tab::make(__('resources.repair_orders.tabs.in_progress'))
                ->query(fn (Builder $query): Builder => $query->whereIn('status', ['in_repair']))
                ->badge(RepairOrder::query()->whereIn('status', ['in_repair'])->count())
                ->badgeColor('info'),
            'ready' => Tab::make(__('resources.repair_orders.tabs.ready'))
                ->query(fn (Builder $query): Builder => $query->where('status', 'ready_for_delivery'))
                ->badge(RepairOrder::query()->where('status', 'ready_for_delivery')->count())
                ->badgeColor('success'),
            'delivered' => Tab::make(__('resources.repair_orders.tabs.delivered'))
                ->query(fn (Builder $query): Builder => $query->where('status', 'delivered'))
                ->badge(RepairOrder::query()->where('status', 'delivered')->count()),
            'closed' => Tab::make(__('resources.repair_orders.tabs.closed'))
                ->query(fn (Builder $query): Builder => $query->whereIn('status', ['cancelled', 'beyond_repair']))
                ->badge(RepairOrder::query()->whereIn('status', ['cancelled', 'beyond_repair'])->count())
                ->badgeColor('danger'),
        ];
    }
}
