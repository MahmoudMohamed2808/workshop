<?php

namespace App\Filament\Resources\Users\Tables;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class UsersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('resources.users.table.name'))
                    ->searchable(),
                TextColumn::make('email')
                    ->label(__('resources.users.table.email'))
                    ->searchable(),
                TextColumn::make('roles.name')
                    ->label(__('resources.users.table.roles'))
                    ->badge()
                    ->separator(', ')
                    ->searchable(),
                TextColumn::make('email_verified_at')
                    ->label(__('resources.users.form.email_verified_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label(__('resources.users.table.created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('updated_at')
                    ->label('آخر تحديث')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('two_factor_confirmed_at')
                    ->label('تأكيد المصادقة الثنائية')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                EditAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->emptyStateHeading(__('resources.users.table.empty_heading'))
            ->emptyStateDescription(__('resources.users.table.empty_description'));
    }
}
