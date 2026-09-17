<?php

namespace App\Filament\Resources\Customers\RelationManagers;

use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class WhatsappMessagesRelationManager extends RelationManager
{
    protected static string $relationship = 'whatsappMessages';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('resources.customers.relations.whatsapp_messages.title');
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('resources.customers.relations.whatsapp_messages.title'))
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('type')
                    ->label(__('resources.customers.relations.whatsapp_messages.type'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __("resources.customers.message_types.{$state}")),
                TextColumn::make('message')
                    ->label(__('resources.customers.relations.whatsapp_messages.message'))
                    ->limit(60)
                    ->searchable(),
                TextColumn::make('status')
                    ->label(__('resources.customers.relations.whatsapp_messages.status'))
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => __("resources.customers.message_statuses.{$state}"))
                    ->color(fn (string $state): string => match ($state) {
                        'sent' => 'success',
                        'pending' => 'warning',
                        default => 'danger',
                    }),
                TextColumn::make('sent_at')
                    ->label(__('resources.customers.relations.whatsapp_messages.sent_at'))
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('type')
                    ->label(__('resources.customers.relations.whatsapp_messages.type'))
                    ->options(__('resources.customers.message_types')),
                SelectFilter::make('status')
                    ->label(__('resources.customers.relations.whatsapp_messages.status'))
                    ->options(__('resources.customers.message_statuses')),
            ])
            ->defaultSort('created_at', 'desc');
    }
}
