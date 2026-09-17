<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class InvoicesRelationManager extends RelationManager
{
    protected static string $relationship = 'invoices';

    protected static ?string $recordTitleAttribute = 'invoice_number';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('resources.customers.relations.invoices.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('resources.customers.relations.invoices.title'))
            ->recordTitleAttribute('invoice_number')
            ->columns([
                TextColumn::make('invoice_number')
                    ->label(__('resources.customers.relations.invoices.invoice_number'))
                    ->searchable()
                    ->sortable(),
                TextColumn::make('repairOrder.order_number')
                    ->label(__('resources.customers.relations.invoices.order_number'))
                    ->searchable(),
                TextColumn::make('total')
                    ->label(__('resources.customers.relations.invoices.total'))
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('paid_total')
                    ->label(__('resources.customers.relations.invoices.paid'))
                    ->money('EGP'),
                TextColumn::make('remaining_total')
                    ->label(__('resources.customers.relations.invoices.remaining'))
                    ->money('EGP'),
                TextColumn::make('issued_at')
                    ->label(__('resources.customers.relations.invoices.issued_at'))
                    ->dateTime()
                    ->sortable(),
            ]);
    }
}
