<?php

namespace App\Filament\Resources\RepairOrders\Schemas;

use App\Models\Problem;
use App\Models\SparePart;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Illuminate\Database\Eloquent\Builder;

class RepairOrderForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(__('resources.repair_orders.form.customer_section'))
                    ->schema([
                        Select::make('customer_id')
                            ->label(__('resources.repair_orders.form.customer'))
                            ->placeholder(__('resources.repair_orders.form.customer_placeholder'))
                            ->relationship('customer', 'name')
                            ->searchable(['name', 'phone'])
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label(__('resources.customers.form.name'))
                                    ->required()
                                    ->maxLength(255),
                                TextInput::make('phone')
                                    ->label(__('resources.customers.form.phone'))
                                    ->required()
                                    ->maxLength(20),
                                TextInput::make('address')
                                    ->label(__('resources.customers.form.address'))
                                    ->maxLength(255),
                            ]),
                        Select::make('device_type_id')
                            ->label(__('resources.repair_orders.form.device_type'))
                            ->placeholder(__('resources.repair_orders.form.device_type_placeholder'))
                            ->relationship('deviceType', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->live()
                            ->afterStateUpdated(function (Set $set): void {
                                $set('brand_id', null);
                                $set('device_model_id', null);
                            }),
                        Select::make('brand_id')
                            ->label(__('resources.repair_orders.form.brand'))
                            ->placeholder(__('resources.repair_orders.form.brand_placeholder'))
                            ->relationship('brand', 'name', modifyQueryUsing: fn (Builder $query, Get $get) => $query
                                ->when($get('device_type_id'), fn (Builder $q, $typeId): Builder => $q->where('device_type_id', $typeId)))
                            ->searchable()
                            ->preload()
                            ->live()
                            ->afterStateUpdated(fn (Set $set) => $set('device_model_id', null)),
                        Select::make('device_model_id')
                            ->label(__('resources.repair_orders.form.model'))
                            ->placeholder(__('resources.repair_orders.form.model_placeholder'))
                            ->relationship('deviceModel', 'name', modifyQueryUsing: fn (Builder $query, Get $get) => $query
                                ->when($get('brand_id'), fn (Builder $q, $brandId): Builder => $q->where('brand_id', $brandId)))
                            ->searchable()
                            ->preload()
                            ->disabled(fn (Get $get): bool => blank($get('brand_id'))),
                        // TextInput::make('serial_number')
                        //     ->label(__('resources.repair_orders.form.serial_number'))
                        //     ->maxLength(255),
                        Textarea::make('device_condition')
                            ->label(__('resources.repair_orders.form.device_condition'))
                            ->rows(2)
                            ->columnSpanFull(),
                        Repeater::make('accessories')
                            ->label(__('resources.repair_orders.form.accessories'))
                            ->simple(TextInput::make('item')
                                ->label(__('resources.repair_orders.form.accessories_item'))
                                ->placeholder(__('resources.repair_orders.form.accessories_placeholder'))
                                ->maxLength(255))
                            ->columnSpanFull(),
                        Textarea::make('customer_complaint')
                            ->label(__('resources.repair_orders.form.customer_complaint'))
                            ->rows(3)
                            ->columnSpanFull(),
                    ])
                    ->columns(2),

                Section::make(__('resources.repair_orders.form.problems_section'))
                    ->description(__('resources.repair_orders.form.problems_section_hint'))
                    ->schema([
                        Repeater::make('orderProblems')
                            ->relationship()
                            ->label(__('resources.repair_orders.form.problems'))
                            ->schema([
                                Select::make('problem_id')
                                    ->label(__('resources.repair_orders.relations.problems.problem'))
                                    ->relationship('problem', 'name', modifyQueryUsing: fn (Builder $query, Get $get) => $query
                                        ->where('is_active', true)
                                        ->when(
                                            $get('../../device_type_id'),
                                            fn (Builder $q, $typeId): Builder => $q->where('device_type_id', $typeId)
                                        ))
                                    ->searchable(['name'])
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->afterStateUpdated(function ($state, Set $set, Get $get): void {
                                        if (! $state) {
                                            return;
                                        }

                                        $problem = Problem::query()->find($state);

                                        if ($problem === null) {
                                            return;
                                        }

                                        $cost = $problem->default_cost_max ?? $problem->default_cost_min;

                                        if ($cost !== null) {
                                            $set('cost', (float) $cost);
                                        }

                                        // Suggest technician from the problem when none is chosen yet.
                                        if (blank($get('../../technician_id')) && $problem->suggested_technician_id !== null) {
                                            $set('../../technician_id', $problem->suggested_technician_id);
                                        }
                                    }),
                                TextInput::make('cost')
                                    ->label(__('resources.repair_orders.relations.problems.cost'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->required()
                                    ->live()
                                    ->default(0),
                                Textarea::make('notes')
                                    ->label(__('resources.repair_orders.relations.problems.notes'))
                                    ->rows(2)
                                    ->columnSpanFull(),
                            ])
                            ->addActionLabel(__('resources.repair_orders.form.add_problem'))
                            ->columns(2)
                            ->columnSpanFull(),
                    ]),

                Section::make(__('resources.repair_orders.form.diagnosis_section'))
                    ->schema([
                        Select::make('technician_id')
                            ->label(__('resources.repair_orders.form.technician'))
                            ->placeholder(__('resources.repair_orders.form.technician_placeholder'))
                            ->relationship('technician', 'name')
                            ->searchable()
                            ->preload(),
                        DatePicker::make('estimated_completion_date')
                            ->label(__('resources.repair_orders.form.estimated_completion_date')),
                        Textarea::make('technician_diagnosis')
                            ->label(__('resources.repair_orders.form.technician_diagnosis'))
                            ->rows(3)
                            ->columnSpanFull(),
                        TextInput::make('labor_cost')
                            ->label(__('resources.repair_orders.form.labor_cost'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->live(),
                        TextInput::make('additional_cost')
                            ->label(__('resources.repair_orders.form.additional_cost'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->live(),
                        TextInput::make('discount')
                            ->label(__('resources.repair_orders.form.discount'))
                            ->numeric()
                            ->minValue(0)
                            ->default(0)
                            ->live(),
                    ])
                    ->columns(2),

                Section::make(__('resources.repair_orders.form.parts_section'))
                    ->description(__('resources.repair_orders.form.parts_section_hint'))
                    ->schema([
                        Repeater::make('orderParts')
                            ->relationship()
                            ->label(__('resources.repair_orders.form.parts'))
                            ->schema([
                                Select::make('spare_part_id')
                                    ->label(__('resources.repair_orders.relations.parts.part'))
                                    ->relationship('sparePart', 'name')
                                    ->searchable(['name', 'code'])
                                    ->preload()
                                    ->required()
                                    ->live()
                                    ->distinct()
                                    ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                    ->afterStateUpdated(function ($state, Set $set): void {
                                        if (! $state) {
                                            return;
                                        }

                                        $part = SparePart::query()->find($state);

                                        if ($part !== null) {
                                            $set('unit_price', (float) $part->selling_price);
                                        }
                                    }),
                                TextInput::make('quantity')
                                    ->label(__('resources.repair_orders.relations.parts.quantity'))
                                    ->numeric()
                                    ->minValue(1)
                                    ->default(1)
                                    ->required()
                                    ->live()
                                    ->maxValue(fn (Get $get): ?int => ($id = $get('spare_part_id'))
                                        ? max(1, (int) SparePart::query()->whereKey($id)->value('quantity'))
                                        : null)
                                    ->helperText(function (Get $get): string {
                                        if (! $get('spare_part_id')) {
                                            return '';
                                        }

                                        $available = (int) SparePart::query()->whereKey($get('spare_part_id'))->value('quantity');

                                        return __('resources.repair_orders.relations.parts.available', ['qty' => $available]);
                                    }),
                                TextInput::make('unit_price')
                                    ->label(__('resources.repair_orders.relations.parts.unit_price'))
                                    ->numeric()
                                    ->minValue(0)
                                    ->required()
                                    ->live(),
                            ])
                            ->addActionLabel(__('resources.repair_orders.form.add_part'))
                            ->columns(3)
                            ->columnSpanFull(),
                    ]),
            ]);
    }
}
