<?php

namespace App\Filament\Resources\SpareParts\RelationManagers;

use App\Filament\Resources\RepairOrders\RepairOrderStatus;
use App\Models\RepairOrder;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class UsageHistoryRelationManager extends RelationManager
{
    protected static string $relationship = 'repairOrders';

    protected static ?string $recordTitleAttribute = 'order_number';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('resources.spare_parts.relations.usage_history.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('resources.spare_parts.relations.usage_history.title'))
            ->recordTitleAttribute('order_number')
            ->columns([
                TextColumn::make('order_number')
                    ->label(__('resources.spare_parts.relations.usage_history.order_number'))
                    ->searchable()
                    ->copyable(),
                TextColumn::make('customer.name')
                    ->label(__('resources.spare_parts.relations.usage_history.customer'))
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('resources.spare_parts.relations.usage_history.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => RepairOrderStatus::label($state))
                    ->color(fn (string $state): string => RepairOrderStatus::color($state)),
                TextColumn::make('consumed_quantity')
                    ->label(__('resources.spare_parts.relations.usage_history.quantity'))
                    ->getStateUsing(fn (RepairOrder $record): int => (int) $record->pivot->quantity),
                TextColumn::make('consumed_unit_price')
                    ->label(__('resources.spare_parts.relations.usage_history.unit_price'))
                    ->money('EGP')
                    ->getStateUsing(fn (RepairOrder $record): float => (float) $record->pivot->unit_price),
                TextColumn::make('consumed_total')
                    ->label(__('resources.spare_parts.relations.usage_history.total'))
                    ->money('EGP')
                    ->getStateUsing(fn (RepairOrder $record): float => (float) $record->pivot->quantity * (float) $record->pivot->unit_price),
                TextColumn::make('created_at')
                    ->label(__('resources.spare_parts.relations.usage_history.used_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->emptyStateHeading(__('resources.spare_parts.relations.usage_history.empty_heading'))
            ->emptyStateDescription(__('resources.spare_parts.relations.usage_history.empty_description'));
    }
}
