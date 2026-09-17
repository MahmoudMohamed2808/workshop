<?php

namespace App\Filament\Resources\RepairOrders\RelationManagers;

use App\Filament\Resources\RepairOrders\RepairOrderStatus;
use App\Models\RepairOrderPart;
use App\Models\SparePart;
use App\Services\CostCalculatorService;
use App\Services\InventoryService;
use App\Services\RepairOrderWorkflowService;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class PartsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderParts';

    protected static ?string $recordTitleAttribute = 'id';

    /** @var array<string, mixed>|null */
    protected ?array $editSnapshot = null;

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('resources.repair_orders.relations.parts.title');
    }

    protected function isLocked(): bool
    {
        $owner = $this->getOwnerRecord();

        return RepairOrderStatus::isTerminal($owner->status)
            || $owner->invoice()->exists();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('spare_part_id')
                    ->label(__('resources.repair_orders.relations.parts.part'))
                    ->relationship('sparePart', 'name')
                    ->searchable(['name', 'code'])
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set): void {
                        if (! $state) {
                            return;
                        }

                        $part = SparePart::query()->find($state);

                        if ($part !== null) {
                            $set('unit_price', $part->selling_price);
                        }
                    }),
                TextInput::make('quantity')
                    ->label(__('resources.repair_orders.relations.parts.quantity'))
                    ->numeric()
                    ->minValue(1)
                    ->maxValue(fn (Get $get): ?int => ($id = $get('spare_part_id'))
                        ? max(1, (int) SparePart::query()->whereKey($id)->value('quantity'))
                        : null)
                    ->helperText(function (Get $get): string {
                        if (! $get('spare_part_id')) {
                            return '';
                        }

                        $available = (int) SparePart::query()->whereKey($get('spare_part_id'))->value('quantity');

                        return __('resources.repair_orders.relations.parts.available', ['qty' => $available]);
                    })
                    ->default(1)
                    ->required(),
                TextInput::make('unit_price')
                    ->label(__('resources.repair_orders.relations.parts.unit_price'))
                    ->numeric()
                    ->minValue(0)
                    ->required(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('resources.repair_orders.relations.parts.title'))
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('sparePart.name')
                    ->label(__('resources.repair_orders.relations.parts.part'))
                    ->searchable(),
                TextColumn::make('quantity')
                    ->label(__('resources.repair_orders.relations.parts.quantity'))
                    ->sortable(),
                TextColumn::make('unit_price')
                    ->label(__('resources.repair_orders.relations.parts.unit_price'))
                    ->money('EGP'),
                TextColumn::make('line_total')
                    ->label(__('resources.repair_orders.relations.parts.total'))
                    ->money('EGP')
                    ->getStateUsing(fn (Model $record): float => (float) $record->quantity * (float) $record->unit_price),
            ])
            ->headerActions([
                CreateAction::make()
                    ->disabled(fn (): bool => $this->isLocked())
                    ->before(function (array $data): void {
                        $owner = $this->getOwnerRecord();

                        if ($owner->orderParts()->where('spare_part_id', $data['spare_part_id'])->exists()) {
                            throw ValidationException::withMessages([
                                'spare_part_id' => __('resources.repair_orders.relations.parts.duplicate'),
                            ]);
                        }

                        $available = (int) SparePart::query()->whereKey($data['spare_part_id'])->value('quantity');

                        if ((int) $data['quantity'] > $available) {
                            throw ValidationException::withMessages([
                                'quantity' => __('resources.repair_orders.relations.parts.insufficient_stock', ['qty' => $available]),
                            ]);
                        }
                    })
                    ->after(function (CostCalculatorService $costs): void {
                        $costs->recalculateOrderTotal($this->getOwnerRecord()->refresh());
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->disabled(fn (): bool => $this->isLocked())
                    ->before(function (RepairOrderPart $record): void {
                        $this->editSnapshot = $record->only(['spare_part_id', 'quantity']);
                    })
                    ->after(function (RepairOrderPart $record, CostCalculatorService $costs, InventoryService $inventory): void {
                        $owner = $this->getOwnerRecord()->refresh();
                        $old = $this->editSnapshot ?? [];
                        $this->editSnapshot = null;

                        // Post-approval quantity changes adjust real stock by the diff.
                        if (RepairOrderWorkflowService::isStockDeducted($owner->status)
                            && ($old['spare_part_id'] ?? null) === $record->spare_part_id
                            && isset($old['quantity'])) {
                            $diff = (int) $record->quantity - (int) $old['quantity'];

                            if ($diff !== 0) {
                                $part = $record->sparePart;

                                if ($diff > 0 && $part->quantity < $diff) {
                                    throw ValidationException::withMessages([
                                        'quantity' => __('resources.repair_orders.relations.parts.insufficient_stock', ['qty' => $part->quantity]),
                                    ]);
                                }

                                $inventory->adjustStock(
                                    $part,
                                    -$diff,
                                    $diff > 0
                                        ? "Increased usage on repair order [{$owner->order_number}]."
                                        : "Decreased usage on repair order [{$owner->order_number}]."
                                );
                            }
                        }

                        $costs->recalculateOrderTotal($owner);
                    }),
                DeleteAction::make()
                    ->disabled(fn (): bool => $this->isLocked())
                    ->requiresConfirmation()
                    ->after(function (RepairOrderPart $record, CostCalculatorService $costs, InventoryService $inventory): void {
                        $owner = $this->getOwnerRecord()->refresh();

                        // Post-approval removal restores the deducted stock.
                        if (RepairOrderWorkflowService::isStockDeducted($owner->status) && $record->sparePart) {
                            $inventory->adjustStock(
                                $record->sparePart,
                                $record->quantity,
                                "Restored removed part from repair order [{$owner->order_number}]."
                            );
                        }

                        $costs->recalculateOrderTotal($owner);
                    }),
            ]);
    }
}
