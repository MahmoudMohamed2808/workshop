<?php

namespace App\Filament\Actions;

use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Illuminate\Database\Eloquent\Model;

class ToggleActiveAction extends Action
{
    public static function getDefaultName(): ?string
    {
        return 'toggleActive';
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this
            ->label(fn (Model $record): string => $record->is_active
                ? __('resources.common.actions.toggle_active.deactivate')
                : __('resources.common.actions.toggle_active.activate'))
            ->icon('heroicon-o-power')
            ->color(fn (Model $record): string => $record->is_active ? 'warning' : 'success')
            ->action(function (Model $record): void {
                $wasActive = (bool) $record->is_active;

                $record->update(['is_active' => ! $wasActive]);

                Notification::make()
                    ->success()
                    ->title($wasActive
                        ? __('resources.common.actions.toggle_active.deactivated')
                        : __('resources.common.actions.toggle_active.activated'))
                    ->send();
            });
    }
}
