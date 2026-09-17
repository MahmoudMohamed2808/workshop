<?php

namespace App\Filament\Resources\RepairOrders\RelationManagers;

use App\Models\User;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class InvoiceRelationManager extends RelationManager
{
    protected static string $relationship = 'invoice';

    protected static ?string $recordTitleAttribute = 'invoice_number';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        // الفني لا يرى الفواتير — الإدارة والموظف فقط.
        $user = auth()->user();

        return ! ($user instanceof User && $user->isTechnician());
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('resources.repair_orders.relations.invoice.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('resources.repair_orders.relations.invoice.title'))
            ->recordTitleAttribute('invoice_number')
            ->columns([
                TextColumn::make('invoice_number')
                    ->label(__('resources.repair_orders.relations.invoice.invoice_number'))
                    ->searchable()
                    ->copyable(),
                TextColumn::make('total')
                    ->label(__('resources.repair_orders.relations.invoice.total'))
                    ->money('EGP'),
                TextColumn::make('paid_total')
                    ->label(__('resources.repair_orders.relations.invoice.paid'))
                    ->money('EGP'),
                TextColumn::make('remaining_total')
                    ->label(__('resources.repair_orders.relations.invoice.remaining'))
                    ->money('EGP'),
                TextColumn::make('issued_at')
                    ->label(__('resources.repair_orders.relations.invoice.issued_at'))
                    ->dateTime(),
            ]);
    }
}
