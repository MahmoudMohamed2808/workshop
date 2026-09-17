<?php

namespace App\Filament\Resources\Customers\Actions;

use App\Models\Customer;
use App\Services\WhatsappService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class SendWhatsappMessageAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'sendWhatsappMessage';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (): string => __('resources.customers.actions.send_whatsapp.label'))
            ->icon('heroicon-o-chat-bubble-left-ellipsis')
            ->modalHeading(fn (Customer $record): string => __('resources.customers.actions.send_whatsapp.heading', ['name' => $record->name]))
            ->modalSubmitActionLabel(fn (): string => __('resources.customers.actions.send_whatsapp.submit'))
            ->form(fn (): array => [
                Textarea::make('message')
                    ->label(fn (): string => __('resources.customers.actions.send_whatsapp.message'))
                    ->placeholder(fn (): string => __('resources.customers.actions.send_whatsapp.message_placeholder'))
                    ->required()
                    ->maxLength(1000)
                    ->rows(4)
                    ->columnSpanFull(),
            ])
            ->action(function (Customer $record, array $data): void {
                app(WhatsappService::class)->send($record, $data['message']);
            })
            ->successNotification(fn (): Notification => Notification::make()
                ->success()
                ->title(__('resources.customers.actions.send_whatsapp.success')));
    }
}
