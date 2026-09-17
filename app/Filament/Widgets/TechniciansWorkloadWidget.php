<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\Technicians\TechnicianResource;
use App\Models\User;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class TechniciansWorkloadWidget extends TableWidget
{
    protected static ?int $sort = 3;

    public function table(Table $table): Table
    {
        return $table
            ->query(
                User::technicians()
                    ->withWorkloadCounts()
                    ->orderByDesc('active_orders_count')
                    ->limit(10)
            )
            ->heading(__('dashboard.technicians_workload.title'))
            ->columns([
                TextColumn::make('name')
                    ->label(__('resources.technicians.table.name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('resources.technicians.table.email'))
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('active_orders_count')
                    ->label(__('dashboard.technicians_workload.active'))
                    ->badge()
                    ->color('primary'),
                TextColumn::make('ready_orders_count')
                    ->label(__('dashboard.technicians_workload.ready'))
                    ->badge()
                    ->color('success'),
            ])
            ->recordUrl(fn (User $record): string => TechnicianResource::getUrl('edit', ['record' => $record]))
            ->paginated(false)
            ->emptyStateHeading(__('dashboard.technicians_workload.empty_heading'))
            ->emptyStateDescription(__('dashboard.technicians_workload.empty_description'));
    }
}
