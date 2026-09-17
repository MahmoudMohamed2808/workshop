<?php

namespace App\Filament\Widgets;

use App\Models\Invoice;
use App\Models\Payment;
use App\Models\RepairOrder;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class TodayOverviewWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $today = today();

        $received = RepairOrder::query()->where('status', 'received')->count();
        $inRepair = RepairOrder::query()->where('status', 'in_repair')->count();
        $ready = RepairOrder::query()->where('status', 'ready_for_delivery')->count();
        $deliveredToday = RepairOrder::query()
            ->where('status', 'delivered')
            ->whereDate('delivered_at', $today)
            ->count();

        $revenueToday = (float) Invoice::query()->whereDate('issued_at', $today)->sum('total');
        $paidToday = (float) Payment::query()->whereDate('paid_at', $today)->sum('amount');
        $outstanding = (float) Invoice::query()->where('remaining_total', '>', 0)->sum('remaining_total');

        return [
            Stat::make(__('dashboard.overview.received'), $received)
                ->description(__('dashboard.overview.received_description'))
                ->descriptionIcon('heroicon-o-inbox-arrow-down')
                ->color('gray'),
            Stat::make(__('dashboard.overview.in_repair'), $inRepair)
                ->description(__('dashboard.overview.in_repair_description'))
                ->descriptionIcon('heroicon-o-wrench-screwdriver')
                ->color('primary'),
            Stat::make(__('dashboard.overview.ready'), $ready)
                ->description(__('dashboard.overview.ready_description'))
                ->descriptionIcon('heroicon-o-check-badge')
                ->color('success'),
            Stat::make(__('dashboard.overview.delivered_today'), $deliveredToday)
                ->description(__('dashboard.overview.delivered_today_description'))
                ->descriptionIcon('heroicon-o-hand-raised')
                ->color('success'),
            Stat::make(__('dashboard.overview.revenue_today'), number_format($revenueToday, 2))
                ->description(__('dashboard.overview.revenue_today_description'))
                ->descriptionIcon('heroicon-o-banknotes')
                ->color('info'),
            Stat::make(__('dashboard.overview.paid_today'), number_format($paidToday, 2))
                ->description(__('dashboard.overview.paid_today_description'))
                ->descriptionIcon('heroicon-o-currency-pound')
                ->color('success'),
            Stat::make(__('dashboard.overview.outstanding'), number_format($outstanding, 2))
                ->description(__('dashboard.overview.outstanding_description'))
                ->descriptionIcon('heroicon-o-exclamation-triangle')
                ->color($outstanding > 0 ? 'danger' : 'success'),
        ];
    }
}
