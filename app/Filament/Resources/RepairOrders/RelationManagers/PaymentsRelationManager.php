<?php

namespace App\Filament\Resources\RepairOrders\RelationManagers;

use App\Models\Invoice;
use App\Models\Payment;
use App\Services\InvoiceService;
use App\Services\PaymentService;
use Filament\Actions\Action;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class PaymentsRelationManager extends RelationManager
{
    protected static string $relationship = 'payments';

    protected static ?string $recordTitleAttribute = 'id';

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        // المالية للموظف والإدارة فقط — الفني لا يرى المدفوعات.
        return auth()->user()?->can('viewAny', Payment::class) ?? false;
    }

    public static function getTitle(Model $ownerRecord, string $pageClass): string
    {
        return __('resources.repair_orders.relations.payments.title');
    }

    public function isReadOnly(): bool
    {
        // Payments are collected from the view page itself (partial
        // payments, collecting the remainder, editing a payment), so this
        // manager opts out of the panel default that makes relation
        // managers read-only on resource view pages.
        return false;
    }

    protected function ownerInvoice(): ?Invoice
    {
        $owner = $this->getOwnerRecord();

        if ($owner === null) {
            return null;
        }

        if ($owner->relationLoaded('invoice')) {
            return $owner->invoice;
        }

        return Invoice::query()->where('repair_order_id', $owner->getKey())->first();
    }

    protected function remainingBalance(): float
    {
        return (float) ($this->ownerInvoice()?->remaining_total ?? 0);
    }

    /**
     * المتبقي المتوقع بعد حفظ هذه الدفعة.
     *
     * عند التعديل يُضاف مبلغ السجل الأصلي أولًا حتى لا يُخصم مرتين.
     */
    protected function remainingAfter(mixed $enteredAmount, ?Model $record): float
    {
        $remaining = max(0, $this->remainingBalance());
        $original = (float) ($record?->amount ?? 0);

        return max(0, $remaining + $original - (float) ($enteredAmount ?? 0));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Placeholder::make('balance_preview')
                    ->label(__('resources.repair_orders.relations.payments.balance_preview'))
                    ->content(function (Get $get, ?Model $record): string {
                        $invoice = $this->ownerInvoice();
                        $total = (float) ($invoice?->total ?? 0);
                        $paid = (float) ($invoice?->paid_total ?? 0);
                        $remaining = max(0, (float) ($invoice?->remaining_total ?? 0));

                        return __('resources.repair_orders.relations.payments.balance_preview_content', [
                            'total' => number_format($total, 2),
                            'paid' => number_format($paid, 2),
                            'remaining' => number_format($remaining, 2),
                            'after' => number_format($this->remainingAfter($get('amount'), $record), 2),
                        ]);
                    })
                    ->columnSpanFull(),
                TextInput::make('amount')
                    ->label(__('resources.repair_orders.relations.payments.amount'))
                    ->numeric()
                    ->minValue(0.01)
                    ->maxValue(fn (?Model $record): float => max(0, $this->remainingBalance() + (float) ($record?->amount ?? 0)))
                    ->default(fn (): float => max(0, $this->remainingBalance()))
                    ->helperText(fn (Get $get, ?Model $record): string => __('resources.repair_orders.relations.payments.remaining_after', [
                        'amount' => number_format($this->remainingAfter($get('amount'), $record), 2),
                    ]))
                    ->live()
                    ->required(),
                Select::make('method')
                    ->label(__('resources.repair_orders.relations.payments.method'))
                    ->options([
                        'cash' => 'نقدي',
                        'vodafone_cash' => 'فودافون كاش',
                        'bank_transfer' => 'تحويل بنكي',
                    ])
                    ->default('cash')
                    ->required()
                    ->live(),
                TextInput::make('reference_number')
                    ->label(__('resources.repair_orders.relations.payments.reference_number'))
                    ->visible(fn (Get $get): bool => $get('method') !== 'cash')
                    ->required(fn (Get $get): bool => $get('method') !== 'cash'),
                DateTimePicker::make('paid_at')
                    ->label(__('resources.repair_orders.relations.payments.paid_at'))
                    ->default(now())
                    ->required(),
                Textarea::make('notes')
                    ->label(__('resources.repair_orders.relations.payments.notes'))
                    ->rows(2)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->heading(__('resources.repair_orders.relations.payments.title'))
            ->description(function (): ?string {
                $invoice = $this->ownerInvoice();

                if ($invoice === null) {
                    return __('resources.repair_orders.relations.payments.no_invoice_hint');
                }

                return __('resources.repair_orders.relations.payments.summary', [
                    'paid' => number_format((float) $invoice->paid_total, 2),
                    'remaining' => number_format((float) $invoice->remaining_total, 2),
                    'total' => number_format((float) $invoice->total, 2),
                ]);
            })
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('amount')
                    ->label(__('resources.repair_orders.relations.payments.amount'))
                    ->money('EGP')
                    ->sortable(),
                TextColumn::make('method')
                    ->label(__('resources.repair_orders.relations.payments.method'))
                    ->badge()
                    ->formatStateUsing(fn (?string $state): string => match ($state) {
                        'cash' => 'نقدي',
                        'vodafone_cash' => 'فودافون كاش',
                        'bank_transfer' => 'تحويل بنكي',
                        default => $state ?? '—',
                    })
                    ->color(fn (?string $state): string => match ($state) {
                        'cash' => 'success',
                        'vodafone_cash' => 'danger',
                        'bank_transfer' => 'info',
                        default => 'gray',
                    }),
                TextColumn::make('reference_number')
                    ->label(__('resources.repair_orders.relations.payments.reference_number'))
                    ->placeholder('—')
                    ->copyable()
                    ->toggleable(),
                TextColumn::make('receivedBy.name')
                    ->label(__('resources.repair_orders.relations.payments.received_by'))
                    ->placeholder('—')
                    ->toggleable(),
                TextColumn::make('paid_at')
                    ->label(__('resources.repair_orders.relations.payments.paid_at'))
                    ->dateTime()
                    ->sortable(),
                TextColumn::make('notes')
                    ->label(__('resources.repair_orders.relations.payments.notes'))
                    ->limit(50)
                    ->placeholder('—'),
            ])
            ->headerActions([
                CreateAction::make()
                    ->label(__('resources.repair_orders.relations.payments.create'))
                    ->disabled(fn (): bool => $this->ownerInvoice() === null || $this->remainingBalance() <= 0)
                    ->mutateFormDataUsing(function (array $data): array {
                        // payments is a HasManyThrough relation: the invoice
                        // key cannot be inferred, so it is set explicitly.
                        $data['invoice_id'] = $this->ownerInvoice()?->id;
                        $data['received_by'] = auth()->id();

                        return $data;
                    })
                    ->before(function (array $data): void {
                        $remaining = max(0, (float) ($this->ownerInvoice()?->fresh()?->remaining_total ?? 0));

                        if ((float) ($data['amount'] ?? 0) > $remaining) {
                            throw ValidationException::withMessages([
                                'amount' => __('resources.repair_orders.relations.payments.amount_exceeds', [
                                    'max' => number_format($remaining, 2),
                                ]),
                            ]);
                        }
                    })
                    ->after(function (Payment $record, InvoiceService $invoices): void {
                        $invoice = $record->invoice;

                        if ($invoice !== null) {
                            $invoices->recalculatePayments($invoice);
                        }
                    })
                    ->successNotification(fn (): Notification => Notification::make()
                        ->success()
                        ->title(__('resources.repair_orders.relations.payments.record_success'))),
                Action::make('collectRemaining')
                    ->label(__('resources.repair_orders.relations.payments.collect_remaining'))
                    ->icon('heroicon-o-check-badge')
                    ->color('success')
                    ->disabled(fn (): bool => $this->ownerInvoice() === null || $this->remainingBalance() <= 0)
                    ->modalHeading(fn (): string => __('resources.repair_orders.relations.payments.collect_remaining_heading', [
                        'amount' => number_format(max(0, $this->remainingBalance()), 2),
                    ]))
                    ->modalSubmitActionLabel(__('resources.repair_orders.relations.payments.collect_remaining_submit'))
                    ->requiresConfirmation()
                    ->form(fn (): array => [
                        Select::make('method')
                            ->label(__('resources.repair_orders.relations.payments.method'))
                            ->options([
                                'cash' => 'نقدي',
                                'vodafone_cash' => 'فودافون كاش',
                                'bank_transfer' => 'تحويل بنكي',
                            ])
                            ->default('cash')
                            ->required()
                            ->live(),
                        TextInput::make('reference_number')
                            ->label(__('resources.repair_orders.relations.payments.reference_number'))
                            ->visible(fn (Get $get): bool => $get('method') !== 'cash')
                            ->required(fn (Get $get): bool => $get('method') !== 'cash'),
                        DateTimePicker::make('paid_at')
                            ->label(__('resources.repair_orders.relations.payments.paid_at'))
                            ->default(now())
                            ->required(),
                        Textarea::make('notes')
                            ->label(__('resources.repair_orders.relations.payments.notes'))
                            ->rows(2)
                            ->columnSpanFull(),
                    ])
                    ->action(function (array $data, PaymentService $payments): void {
                        $invoice = $this->ownerInvoice();

                        if ($invoice === null || (float) $invoice->remaining_total <= 0) {
                            Notification::make()
                                ->warning()
                                ->title(__('resources.repair_orders.relations.payments.already_paid'))
                                ->send();

                            return;
                        }

                        try {
                            $payments->recordFullPayment($invoice, [
                                ...$data,
                                'received_by' => auth()->id(),
                            ]);

                            Notification::make()
                                ->success()
                                ->title(__('resources.repair_orders.relations.payments.collect_remaining_success'))
                                ->send();
                        } catch (\InvalidArgumentException $e) {
                            Notification::make()
                                ->danger()
                                ->title($e->getMessage())
                                ->send();
                        }
                    }),
            ])
            ->recordActions([
                EditAction::make()
                    ->after(function (Payment $record, InvoiceService $invoices): void {
                        $invoice = $record->invoice;

                        if ($invoice !== null) {
                            $invoices->recalculatePayments($invoice);
                        }
                    }),
                DeleteAction::make()
                    ->after(function (Payment $record, InvoiceService $invoices): void {
                        $invoice = $record->invoice;

                        if ($invoice !== null) {
                            $invoices->recalculatePayments($invoice->fresh() ?? $invoice);
                        }
                    }),
            ])
            ->defaultSort('paid_at', 'desc');
    }
}
