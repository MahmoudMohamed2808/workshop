<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\LowStockPartsWidget;
use App\Filament\Widgets\TechniciansWorkloadWidget;
use App\Filament\Widgets\TodayOverviewWidget;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Widgets\Widget;
use Illuminate\Contracts\Support\Htmlable;

class Dashboard extends BaseDashboard
{
    public static function getNavigationLabel(): string
    {
        return __('dashboard.title');
    }

    public function getTitle(): string|Htmlable
    {
        return __('dashboard.title');
    }

    /**
     * @return array<class-string<Widget>>
     */
    public function getWidgets(): array
    {
        return [
            TodayOverviewWidget::class,
            LowStockPartsWidget::class,
            TechniciansWorkloadWidget::class,
        ];
    }

    public function getColumns(): int|array
    {
        return 2;
    }
}
