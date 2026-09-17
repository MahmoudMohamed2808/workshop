<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use App\Filament\Resources\RepairOrders\RepairOrderStatus;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class RepairOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'repairOrders';

    protected static ?string $recordTitleAttribute = 'order_number';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('resources.customers.relations.repair_orders.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('resources.customers.relations.repair_orders.title'))
            ->recordTitleAttribute('order_number')
            ->columns([
                TextColumn::make('order_number')
                    ->label(__('resources.customers.relations.repair_orders.order_number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('status')
                    ->label(__('resources.customers.relations.repair_orders.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => RepairOrderStatus::statusLabel($state))
                    ->color(fn (string $state): string => RepairOrderStatus::color($state)),
                TextColumn::make('deviceType.name')
                    ->label(__('resources.customers.relations.repair_orders.device')),
                TextColumn::make('total_cost')
                    ->label(__('resources.customers.relations.repair_orders.total_cost'))
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('resources.customers.relations.repair_orders.received_at'))
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
