<?php

namespace App\Filament\Resources\RepairOrders\RelationManagers;

use App\Filament\Resources\RepairOrders\RepairOrderStatus;
use App\Models\Problem;
use App\Services\CostCalculatorService;
use App\Services\RepairOrderBundleService;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class ProblemsRelationManager extends RelationManager
{
    protected static string $relationship = 'orderProblems';

    protected static ?string $recordTitleAttribute = 'id';

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('resources.repair_orders.relations.problems.title');
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
                Select::make('problem_id')
                    ->label(__('resources.repair_orders.relations.problems.problem'))
                    ->relationship('problem', 'name')
                    ->searchable(['name'])
                    ->preload()
                    ->required()
                    ->live()
                    ->afterStateUpdated(function ($state, Set $set): void {
                        if (! $state) {
                            return;
                        }

                        $problem = Problem::query()->find($state);

                        if ($problem?->default_cost_max !== null) {
                            $set('cost', $problem->default_cost_max);
                        } elseif ($problem?->default_cost_min !== null) {
                            $set('cost', $problem->default_cost_min);
                        }
                    }),
                TextInput::make('cost')
                    ->label(__('resources.repair_orders.relations.problems.cost'))
                    ->numeric()
                    ->minValue(0)
                    ->required(),
                Textarea::make('notes')
                    ->label(__('resources.repair_orders.relations.problems.notes'))
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('resources.repair_orders.relations.problems.title'))
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('problem.name')
                    ->label(__('resources.repair_orders.relations.problems.problem'))
                    ->searchable(),
                TextColumn::make('cost')
                    ->label(__('resources.repair_orders.relations.problems.cost'))
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('notes')
                    ->label(__('resources.repair_orders.relations.problems.notes'))
                    ->limit(50)
                    ->placeholder('—'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->disabled(fn (): bool => $this->isLocked())
                    ->before(function (array $data): void {
                        if ($this->getOwnerRecord()->orderProblems()->where('problem_id', $data['problem_id'])->exists()) {
                            throw ValidationException::withMessages([
                                'problem_id' => __('resources.repair_orders.relations.problems.duplicate'),
                            ]);
                        }
                    })
                    ->after(function (array $data, RepairOrderBundleService $bundles): void {
                        $owner = $this->getOwnerRecord()->refresh();
                        $problem = Problem::query()->find($data['problem_id']);

                        if ($problem === null) {
                            return;
                        }

                        // The row itself was already created by the action;
                        // apply only the rest of the bundle (accessories, technician, parts).
                        $bundles->applyProblem($owner, $problem, $data['cost'] ?? null, $data['notes'] ?? null);
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->disabled(fn (): bool => $this->isLocked())
                    ->after(function (CostCalculatorService $costs): void {
                        $costs->recalculateOrderTotal($this->getOwnerRecord()->refresh());
                    }),
                DeleteAction::make()
                    ->disabled(fn (): bool => $this->isLocked())
                    ->requiresConfirmation()
                    ->after(function (CostCalculatorService $costs): void {
                        $costs->recalculateOrderTotal($this->getOwnerRecord()->refresh());
                    }),
            ]);
    }
}
