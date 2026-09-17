<?php

namespace App\Filament\Resources\RepairOrders\Actions;

use App\Models\Problem;
use App\Models\RepairOrder;
use App\Services\RepairOrderBundleService;
use Filament\Actions\Action;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;

class RecordDiagnosisAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'recordDiagnosis';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (): string => __('resources.repair_orders.actions.record_diagnosis.label'))
            ->icon('heroicon-o-clipboard-document-check')
            ->color('info')
            ->visible(fn (RepairOrder $record): bool => in_array($record->status, ['received', 'inspection'], true)
                && (auth()->user()?->can('update', $record) ?? false))
            ->modalHeading(fn (): string => __('resources.repair_orders.actions.record_diagnosis.heading'))
            ->modalSubmitActionLabel(fn (): string => __('resources.repair_orders.actions.record_diagnosis.submit'))
            ->form(fn (RepairOrder $record): array => [
                Textarea::make('technician_diagnosis')
                    ->label(fn (): string => __('resources.repair_orders.actions.record_diagnosis.diagnosis'))
                    ->placeholder(fn (): string => __('resources.repair_orders.actions.record_diagnosis.diagnosis_placeholder'))
                    ->default($record->technician_diagnosis)
                    ->required()
                    ->rows(3)
                    ->columnSpanFull(),
                Repeater::make('problems')
                    ->label(fn (): string => __('resources.repair_orders.actions.record_diagnosis.problems'))
                    ->schema([
                        Select::make('problem_id')
                            ->label(fn (): string => __('resources.repair_orders.relations.problems.problem'))
                            ->options(fn (): array => Problem::query()->orderBy('name')->pluck('name', 'id')->all())
                            ->searchable()
                            ->preload()
                            ->required()
                            ->distinct()
                            ->disableOptionsWhenSelectedInSiblingRepeaterItems(),
                        TextInput::make('cost')
                            ->label(fn (): string => __('resources.repair_orders.actions.record_diagnosis.cost'))
                            ->numeric()
                            ->minValue(0)
                            ->required(),
                        TextInput::make('notes')
                            ->label(fn (): string => __('resources.repair_orders.actions.record_diagnosis.notes'))
                            ->maxLength(500),
                    ])
                    ->addActionLabel(fn (): string => __('resources.repair_orders.actions.record_diagnosis.add_problem'))
                    ->columns(3)
                    ->columnSpanFull(),
            ])
            ->action(function (RepairOrder $record, array $data, RepairOrderBundleService $bundles): void {
                abort_unless(auth()->user()?->can('update', $record) ?? false, 403);

                $record->update(['technician_diagnosis' => $data['technician_diagnosis']]);

                $applied = ['problem_added' => 0, 'accessories_added' => 0, 'parts_added' => 0, 'technician_suggested' => false];

                foreach ($data['problems'] ?? [] as $row) {
                    $problem = Problem::query()->find($row['problem_id']);

                    if ($problem === null) {
                        continue;
                    }

                    $one = $bundles->applyProblem($record->refresh(), $problem, $row['cost'], $row['notes'] ?? null);

                    $applied['problem_added'] += $one['problem_added'] ? 1 : 0;
                    $applied['accessories_added'] += $one['accessories_added'];
                    $applied['parts_added'] += $one['parts_added'];
                    $applied['technician_suggested'] = $applied['technician_suggested'] || $one['technician_suggested'];
                }

                if ($applied['parts_added'] > 0 || $applied['accessories_added'] > 0 || $applied['technician_suggested']) {
                    Notification::make()
                        ->success()
                        ->title(__('resources.repair_orders.actions.record_diagnosis.bundle_applied', [
                            'parts' => $applied['parts_added'],
                            'accessories' => $applied['accessories_added'],
                        ]))
                        ->send();
                }
            })
            ->successNotification(fn (): Notification => Notification::make()
                ->success()
                ->title(__('resources.repair_orders.actions.record_diagnosis.success')));
    }
}
