<?php

namespace App\Filament\Resources\RepairOrders;

use App\Filament\Resources\RepairOrders\Pages\CreateRepairOrder;
use App\Filament\Resources\RepairOrders\Pages\EditRepairOrder;
use App\Filament\Resources\RepairOrders\Pages\ListRepairOrders;
use App\Filament\Resources\RepairOrders\Pages\ViewRepairOrder;
use App\Filament\Resources\RepairOrders\RelationManagers\InvoiceRelationManager;
use App\Filament\Resources\RepairOrders\RelationManagers\PartsRelationManager;
use App\Filament\Resources\RepairOrders\RelationManagers\PaymentsRelationManager;
use App\Filament\Resources\RepairOrders\RelationManagers\ProblemsRelationManager;
use App\Filament\Resources\RepairOrders\RelationManagers\WhatsappMessagesRelationManager;
use App\Filament\Resources\RepairOrders\Schemas\RepairOrderForm;
use App\Filament\Resources\RepairOrders\Tables\RepairOrdersTable;
use App\Models\RepairOrder;
use App\Models\User;
use BackedEnum;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class RepairOrderResource extends Resource
{
    protected static ?string $model = RepairOrder::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWrenchScrewdriver;

    protected static ?string $recordTitleAttribute = 'order_number';

    public static function getModelLabel(): string
    {
        return __('resources.repair_orders.label');
    }

    public static function getPluralModelLabel(): string
    {
        return __('resources.repair_orders.plural');
    }

    public static function getNavigationLabel(): string
    {
        return __('resources.repair_orders.navigation');
    }

    public static function form(Schema $schema): Schema
    {
        return RepairOrderForm::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return RepairOrdersTable::configure($table);
    }

    public static function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('resources.repair_orders.infolist.timeline'))
                    ->schema([
                        ViewEntry::make('status_timeline')
                            ->view('filament.repair-orders.status-timeline')
                            ->viewData(fn (RepairOrder $record): array => ['record' => $record])
                            ->columnSpanFull(),
                    ]),
                Section::make(__('resources.repair_orders.infolist.details'))
                    ->schema([
                        TextEntry::make('order_number')
                            ->label(__('resources.repair_orders.infolist.order_number')),
                        TextEntry::make('status')
                            ->label(__('resources.repair_orders.infolist.status'))
                            ->badge()
                            ->formatStateUsing(fn (string $state): string => RepairOrderStatus::label($state))
                            ->color(fn (string $state): string => RepairOrderStatus::color($state)),
                        TextEntry::make('customer.name')
                            ->label(__('resources.repair_orders.infolist.customer')),
                        TextEntry::make('deviceType.name')
                            ->label(__('resources.repair_orders.infolist.device_type')),
                        TextEntry::make('brand.name')
                            ->label(__('resources.repair_orders.infolist.brand'))
                            ->placeholder('—'),
                        TextEntry::make('deviceModel.name')
                            ->label(__('resources.repair_orders.infolist.model'))
                            ->placeholder('—'),
                        TextEntry::make('serial_number')
                            ->label(__('resources.repair_orders.infolist.serial_number'))
                            ->placeholder('—')
                            ->copyable(),
                        TextEntry::make('technician.name')
                            ->label(__('resources.repair_orders.infolist.technician'))
                            ->placeholder('—'),
                        TextEntry::make('device_condition')
                            ->label(__('resources.repair_orders.infolist.device_condition'))
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('accessories')
                            ->label(__('resources.repair_orders.infolist.accessories'))
                            ->getStateUsing(function (RepairOrder $record): string {
                                $accessories = $record->accessories;

                                if (is_string($accessories)) {
                                    $decoded = json_decode($accessories, true);
                                    $accessories = is_array($decoded) ? $decoded : [$accessories];
                                }

                                if (! is_array($accessories)) {
                                    return '—';
                                }

                                $items = array_filter(array_map(
                                    fn (mixed $item): string => is_array($item) ? (string) ($item['item'] ?? '') : (string) $item,
                                    $accessories
                                ));

                                return $items === [] ? '—' : implode('، ', $items);
                            })
                            ->columnSpanFull(),
                        TextEntry::make('customer_complaint')
                            ->label(__('resources.repair_orders.infolist.customer_complaint'))
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('technician_diagnosis')
                            ->label(__('resources.repair_orders.infolist.technician_diagnosis'))
                            ->placeholder('—')
                            ->columnSpanFull(),
                        TextEntry::make('rejection_reason')
                            ->label(__('resources.repair_orders.infolist.rejection_reason'))
                            ->visible(fn (RepairOrder $record): bool => filled($record->rejection_reason))
                            ->columnSpanFull(),
                        TextEntry::make('estimated_completion_date')
                            ->label(__('resources.repair_orders.infolist.estimated_completion_date'))
                            ->date()
                            ->placeholder('—'),
                        TextEntry::make('approved_at')
                            ->label(__('resources.repair_orders.infolist.approved_at'))
                            ->dateTime()
                            ->placeholder('—'),
                        TextEntry::make('delivered_at')
                            ->label(__('resources.repair_orders.infolist.delivered_at'))
                            ->dateTime()
                            ->placeholder('—'),
                    ])
                    ->columns(3),
                Section::make(__('resources.repair_orders.infolist.cost_breakdown'))
                    ->schema([
                        TextEntry::make('problems_total')
                            ->label(__('resources.repair_orders.infolist.problems_total'))
                            ->money('EGP')
                            ->getStateUsing(fn (RepairOrder $record): float => (float) $record->orderProblems()->sum('cost')),
                        TextEntry::make('parts_total')
                            ->label(__('resources.repair_orders.infolist.parts_total'))
                            ->money('EGP')
                            ->getStateUsing(fn (RepairOrder $record): float => (float) $record->orderParts()
                                ->selectRaw('COALESCE(SUM(quantity * unit_price), 0) as aggregate')
                                ->value('aggregate')),
                        TextEntry::make('labor_cost')
                            ->label(__('resources.repair_orders.infolist.labor_cost'))
                            ->money('EGP'),
                        TextEntry::make('additional_cost')
                            ->label(__('resources.repair_orders.infolist.additional_cost'))
                            ->money('EGP'),
                        TextEntry::make('discount')
                            ->label(__('resources.repair_orders.infolist.discount'))
                            ->money('EGP'),
                        TextEntry::make('total_cost')
                            ->label(__('resources.repair_orders.infolist.total_cost'))
                            ->money('EGP')
                            ->weight('bold'),
                    ])
                    ->columns(3),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            ProblemsRelationManager::class,
            PartsRelationManager::class,
            InvoiceRelationManager::class,
            PaymentsRelationManager::class,
            WhatsappMessagesRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListRepairOrders::route('/'),
            'create' => CreateRepairOrder::route('/create'),
            'view' => ViewRepairOrder::route('/{record}'),
            'edit' => EditRepairOrder::route('/{record}/edit'),
        ];
    }

    public static function getGloballySearchableAttributes(): array
    {
        return ['order_number', 'serial_number', 'customer.name', 'customer.phone'];
    }

    public static function getGlobalSearchResultDetails(Model $record): array
    {
        return [
            __('resources.repair_orders.search.order_number') => $record->order_number,
            __('resources.repair_orders.search.customer') => $record->customer?->name,
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()->with(['customer', 'deviceType', 'brand', 'deviceModel', 'technician']);

        // الفني يشوف أوامره المسندة إليه فقط.
        $user = auth()->user();

        if ($user instanceof User && $user->isTechnician()) {
            $query->where('technician_id', $user->getKey());
        }

        return $query;
    }

    public static function getRecordRouteBindingEloquentQuery(): Builder
    {
        return parent::getRecordRouteBindingEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
