<?php

namespace App\Filament\Resources\Customers;

use App\Filament\Resources\Customers\Pages\CreateCustomer;
use App\Filament\Resources\Customers\Pages\EditCustomer;
use App\Filament\Resources\Customers\Pages\ListCustomers;
use App\Filament\Resources\Customers\Pages\ViewCustomer;
use App\Filament\Resources\Customers\RelationManagers\InvoicesRelationManager;
use App\Filament\Resources\Customers\RelationManagers\RepairOrdersRelationManager;
use App\Filament\Resources\Customers\RelationManagers\WhatsappMessagesRelationManager;
use App\Filament\Resources\Customers\Schemas\CustomerForm;
use App\Filament\Resources\Customers\Tables\CustomersTable;
use App\Models\Customer;
use App\Models\Warranty;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CustomerResource extends Resource
{
    protected static ?string $model = Customer::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static ?string $recordTitleAttribute = 'name';

    public static function getModelLabel(): string
    {
        return __('resources.customers.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.customers.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('resources.customers.navigation');
    }

    public static function form(Schema $schema): Schema
    {
        return CustomerForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CustomersTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('resources.customers.infolist.profile'))
                    ->schema([
                        TextEntry::make('name')
                            ->label(__('resources.customers.infolist.name')),
                        TextEntry::make('phone')
                            ->label(__('resources.customers.infolist.phone'))
                            ->copyable(),
                        // TextEntry::make('whatsapp_phone')
                        //     ->label(__('resources.customers.infolist.whatsapp_phone'))
                        //     ->placeholder('—'),
                        TextEntry::make('address')
                            ->label(__('resources.customers.infolist.address'))
                            ->placeholder('—'),
                        TextEntry::make('notes')
                            ->label(__('resources.customers.infolist.notes'))
                            ->placeholder('—')
                            ->columnSpanFull(),
                    ])
                    ->columns(2),
                Section::make(__('resources.customers.infolist.stats'))
                    ->schema([
                        TextEntry::make('total_orders')
                            ->label(__('resources.customers.infolist.total_orders'))
                            ->getStateUsing(fn (Customer $record): int => $record->repairOrders()->count()),
                        TextEntry::make('total_spent')
                            ->label(__('resources.customers.infolist.total_spent'))
                            ->money('EGP')
                            ->getStateUsing(fn (Customer $record): float => (float) $record->invoices()->sum('paid_total')),
                        TextEntry::make('active_warranties')
                            ->label(__('resources.customers.infolist.active_warranties'))
                            ->getStateUsing(fn (Customer $record): int => Warranty::query()
                                ->whereHas('repairOrder', fn (Builder $query): Builder => $query->where('customer_id', $record->id))
                                ->active()
                                ->count()),
                    ])
                    ->columns(3),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            RepairOrdersRelationManager::class,
            InvoicesRelationManager::class,
            WhatsappMessagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCustomers::route('/'),
            'create' => CreateCustomer::route('/create'),
            'view' => ViewCustomer::route('/{record}'),
            'edit' => EditCustomer::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['name', 'phone'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            __('resources.customers.search.phone') => $record->phone,
        ];
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
