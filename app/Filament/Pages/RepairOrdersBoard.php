<?php

namespace App\Filament\Pages;

use App\Filament\Resources\RepairOrders\RepairOrderStatus;
use App\Models\RepairOrder;
use App\Models\User;
use App\Services\RepairOrderWorkflowService;
use BackedEnum;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Database\Eloquent\Collection;
use InvalidArgumentException;

class RepairOrdersBoard extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedViewColumns;

    protected static ?int $navigationSort = 1;

    protected string $view = 'filament.pages.repair-orders-board';

    public static function getNavigationLabel(): string
    {
        return __('dashboard.board.navigation');
    }

    public function getTitle(): string|Htmlable
    {
        return __('dashboard.board.title');
    }

    /**
     * @return array<string, array{label: string, color: string, readonly: bool, total: float, orders: Collection<int, RepairOrder>}>
     */
    public function getBoardColumns(): array
    {
        $columns = [];
        $user = auth()->user();
        $technicianId = $user instanceof User && $user->isTechnician()
            ? $user->getKey()
            : null;

        foreach (RepairOrderStatus::flow() as $status) {
            $orders = RepairOrder::query()
                ->where('status', $status)
                ->when($technicianId !== null, fn ($query) => $query->where('technician_id', $technicianId))
                ->with(['customer', 'deviceType', 'brand', 'deviceModel', 'technician'])
                ->latest()
                ->limit(50)
                ->get();

            $columns[$status] = [
                'label' => RepairOrderStatus::label($status),
                'color' => RepairOrderStatus::color($status),
                'readonly' => $status === RepairOrderStatus::DELIVERED,
                'total' => (float) $orders->sum(fn (RepairOrder $order): float => (float) $order->total_cost),
                'orders' => $orders,
            ];
        }

        return $columns;
    }

    public function move(int $orderId, string $status, RepairOrderWorkflowService $workflow): void
    {
        $order = RepairOrder::query()->findOrFail($orderId);

        // تفويض: الفني يحرك أوامره فقط، والباقي حسب صلاحية التعديل.
        if (auth()->user()?->cannot('update', $order)) {
            Notification::make()
                ->danger()
                ->title(__('dashboard.board.move_not_allowed'))
                ->send();

            return;
        }

        // Delivery needs confirmation (receiver name), so it stays
        // exclusive to ConfirmDeliveryAction on the order page.
        if ($status === RepairOrderStatus::DELIVERED) {
            Notification::make()
                ->danger()
                ->title(__('dashboard.board.delivered_hint'))
                ->send();

            return;
        }

        if (! $workflow->canTransitionTo($order, $status)) {
            Notification::make()
                ->danger()
                ->title(__('dashboard.board.move_not_allowed'))
                ->send();

            return;
        }

        try {
            $workflow->transitionTo($order, $status);
        } catch (InvalidArgumentException) {
            Notification::make()
                ->danger()
                ->title(__('dashboard.board.move_not_allowed'))
                ->send();

            return;
        }

        Notification::make()
            ->success()
            ->title(__('dashboard.board.move_success', [
                'order' => $order->order_number,
                'status' => RepairOrderStatus::label($status),
            ]))
            ->send();
    }
}
