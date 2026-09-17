<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\SpareParts\SparePartResource;
use App\Models\SparePart;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class LowStockPartsWidget extends TableWidget
{
    protected static ?int $sort = 2;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                SparePart::query()
                    ->lowStock()
                    ->orderBy('quantity')
                    ->limit(10)
            )
            ->heading(__('dashboard.low_stock.title'))
            ->columns([
                TextColumn::make('code')
                    ->label(__('resources.spare_parts.table.code'))
                    ->searchable(),
                TextColumn::make('name')
                    ->label(__('resources.spare_parts.table.name'))
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label(__('resources.spare_parts.table.quantity'))
                    ->badge()
                    ->color('danger'),
                TextColumn::make('minimum_stock')
                    ->label(__('resources.spare_parts.table.minimum_stock')),
            ])
            ->recordUrl(fn (SparePart $record): string => SparePartResource::getUrl('edit', ['record' => $record]))
            ->paginated(false)
            ->emptyStateHeading(__('dashboard.low_stock.empty_heading'))
            ->emptyStateDescription(__('dashboard.low_stock.empty_description'));
    }
}
