<?php

namespace App\Filament\Resources\SpareParts\Pages;

use App\Filament\Resources\SpareParts\SparePartResource;
use App\Models\SparePart;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;
use Filament\Schemas\Components\Tabs\Tab;
use Illuminate\Database\Eloquent\Builder;

class ListSpareParts extends ListRecords
{
    protected static string $resource = SparePartResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }

    public function getTabs(): array
    {
        return [
            'all' => Tab::make(__('resources.spare_parts.tabs.all')),
            'low_stock' => Tab::make(__('resources.spare_parts.tabs.low_stock'))
                ->query(fn (Builder $query): Builder => $query->lowStock())
                ->badge(SparePart::query()->lowStock()->count())
                ->badgeColor('danger'),
        ];
    }
}
