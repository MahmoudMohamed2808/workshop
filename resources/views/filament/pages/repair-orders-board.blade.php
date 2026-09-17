@php
    use App\Filament\Resources\RepairOrders\RepairOrderResource;
    use App\Filament\Resources\RepairOrders\RepairOrderStatus;

    $columns = $this->getBoardColumns();
    $boardTotal = collect($columns)->sum(fn ($column) => $column['orders']->count());
    $boardValue = collect($columns)->sum(fn ($column) => $column['total']);

    $dot = [
        'gray' => 'bg-gray-400',
        'warning' => 'bg-amber-500',
        'info' => 'bg-sky-500',
        'primary' => 'bg-indigo-500',
        'success' => 'bg-emerald-500',
        'danger' => 'bg-rose-500',
    ];

    $bar = [
        'gray' => 'from-gray-400 to-gray-300',
        'warning' => 'from-amber-500 to-amber-300',
        'info' => 'from-sky-500 to-sky-300',
        'primary' => 'from-indigo-500 to-indigo-300',
        'success' => 'from-emerald-500 to-emerald-300',
        'danger' => 'from-rose-500 to-rose-300',
    ];
@endphp

<div class="space-y-5">
    {{-- Summary strip --}}
    <div class="grid grid-cols-1 gap-3 sm:grid-cols-3">
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('dashboard.board.title') }}</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">{{ $boardTotal }}</p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('dashboard.board.description') }}</p>
        </div>
        <div class="rounded-2xl bg-white p-4 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/5 dark:ring-white/10">
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400">{{ __('dashboard.overview.outstanding') }}</p>
            <p class="mt-1 text-2xl font-bold text-gray-900 dark:text-white">
                {{ number_format($boardValue, 2) }}
                <span class="text-sm font-medium text-gray-400">{{ __('dashboard.currency') }}</span>
            </p>
            <p class="mt-1 text-xs text-gray-500 dark:text-gray-400">{{ __('dashboard.overview.revenue_today_description') }}</p>
        </div>
        <div class="flex items-center justify-between gap-3 rounded-2xl bg-gradient-to-bl from-primary-600 to-primary-500 p-4 text-white shadow-sm">
            <div>
                <p class="text-sm font-semibold">{{ __('dashboard.board.view_order') }}</p>
                <p class="mt-0.5 text-xs text-white/80">{{ __('dashboard.board.delivered_hint') }}</p>
            </div>
            <a
                href="{{ RepairOrderResource::getUrl('create') }}"
                class="shrink-0 rounded-xl bg-white/15 px-3 py-2 text-xs font-semibold text-white ring-1 ring-white/30 transition hover:bg-white/25"
            >
                + {{ __('dashboard.board.title') }}
            </a>
        </div>
    </div>

    {{-- Kanban columns --}}
    <div class="flex items-start gap-4 overflow-x-auto pb-4">
        @foreach ($columns as $status => $column)
            <section
                wire:key="board-column-{{ $status }}"
                @if (! $column['readonly'])
                    x-data="{ draggingOver: false }"
                    x-on:dragover.prevent="draggingOver = true"
                    x-on:dragleave="draggingOver = false"
                    x-on:drop="draggingOver = false; $wire.move(parseInt($event.dataTransfer.getData('text/plain')), '{{ $status }}')"
                    :class="draggingOver && 'ring-2 ring-primary-500 ring-offset-1 scale-[1.01]'"
                @endif
                class="w-80 shrink-0 overflow-hidden rounded-2xl bg-gray-100 shadow-sm ring-1 ring-gray-950/5 transition dark:bg-white/5 dark:ring-white/10"
            >
                <div class="h-1.5 bg-gradient-to-l {{ $bar[$column['color']] ?? $bar['gray'] }}"></div>

                <header class="flex items-center justify-between gap-2 px-4 pt-3">
                    <h3 class="flex items-center gap-2 text-sm font-bold text-gray-800 dark:text-gray-100">
                        <span class="inline-block size-2.5 rounded-full {{ $dot[$column['color']] ?? $dot['gray'] }}"></span>
                        {{ $column['label'] }}
                    </h3>

                    <div class="flex items-center gap-1.5">
                        <span class="rounded-full bg-white px-2.5 py-0.5 text-xs font-bold text-gray-700 shadow-sm ring-1 ring-gray-950/5 dark:bg-white/10 dark:text-gray-200 dark:ring-white/10">
                            {{ $column['orders']->count() }}
                        </span>
                        @if ($column['total'] > 0)
                            <span class="rounded-full bg-gray-900/[0.04] px-2 py-0.5 text-[11px] font-medium text-gray-500 dark:bg-white/10 dark:text-gray-300">
                                {{ number_format($column['total'], 0) }} {{ __('dashboard.currency') }}
                            </span>
                        @endif
                    </div>
                </header>

                <div class="max-h-[65vh] space-y-2.5 overflow-y-auto p-3">
                    @forelse ($column['orders'] as $order)
                        @php
                            $isLate = $order->estimated_completion_date
                                && $order->estimated_completion_date->isPast()
                                && ! RepairOrderStatus::isTerminal($order->status);
                            $initial = mb_substr($order->customer?->name ?? '؟', 0, 1);
                            $deviceLine = trim(implode(' · ', array_filter([
                                $order->deviceType?->name,
                                $order->brand?->name,
                                $order->deviceModel?->name,
                            ]))) ?: '—';
                        @endphp
                        <article
                            wire:key="board-card-{{ $order->id }}"
                            @if (! RepairOrderStatus::isTerminal($order->status))
                                draggable="true"
                                x-on:dragstart="$event.dataTransfer.setData('text/plain', '{{ $order->id }}'); $event.dataTransfer.effectAllowed = 'move'"
                            @endif
                            class="group rounded-xl bg-white p-3 shadow-sm ring-1 transition hover:shadow-md hover:ring-primary-200 active:cursor-grabbing dark:bg-white/[0.07] dark:ring-white/10 dark:hover:ring-primary-400/30 {{ RepairOrderStatus::isTerminal($order->status) ? '' : 'cursor-grab' }} {{ $isLate ? 'ring-rose-300 dark:ring-rose-400/40' : 'ring-gray-950/5' }}"
                        >
                            <div class="flex items-center justify-between gap-2">
                                <span class="rounded-lg bg-gray-100 px-2 py-0.5 font-mono text-xs font-bold text-gray-800 dark:bg-white/10 dark:text-gray-100">
                                    {{ $order->order_number }}
                                </span>
                                <a
                                    href="{{ RepairOrderResource::getUrl('view', ['record' => $order]) }}"
                                    class="rounded-lg px-2 py-1 text-[11px] font-semibold text-primary-600 transition hover:bg-primary-50 hover:underline dark:text-primary-400 dark:hover:bg-white/10"
                                >
                                    {{ __('dashboard.board.view_order') }} ←
                                </a>
                            </div>

                            <div class="mt-2.5 flex items-center gap-2">
                                <span class="flex size-8 shrink-0 items-center justify-center rounded-full bg-gradient-to-br from-primary-500 to-primary-700 text-sm font-bold text-white">
                                    {{ $initial }}
                                </span>
                                <div class="min-w-0">
                                    <p class="truncate text-[13px] font-semibold text-gray-900 dark:text-white">
                                        {{ $order->customer?->name ?? '—' }}
                                    </p>
                                    <p class="truncate text-[11px] text-gray-500 dark:text-gray-400">
                                        {{ $deviceLine }}
                                    </p>
                                </div>
                            </div>

                            <div class="mt-2.5 flex items-center justify-between border-t border-dashed border-gray-200 pt-2 text-[11px] dark:border-white/10">
                                <span class="font-bold text-gray-800 dark:text-gray-100">
                                    {{ number_format((float) $order->total_cost, 0) }}
                                    <span class="font-medium text-gray-400">{{ __('dashboard.currency') }}</span>
                                </span>
                                <span class="flex items-center gap-1.5 text-gray-500 dark:text-gray-400">
                                    @if ($order->technician?->name)
                                        <span class="max-w-24 truncate">{{ $order->technician->name }}</span>
                                        <span>·</span>
                                    @endif
                                    <span>{{ $order->created_at?->diffForHumans() }}</span>
                                </span>
                            </div>

                            @if ($isLate)
                                <p class="mt-1.5 rounded-lg bg-rose-50 px-2 py-1 text-[11px] font-semibold text-rose-600 dark:bg-rose-500/10 dark:text-rose-300">
                                    ⚠ {{ $order->estimated_completion_date->format('Y-m-d') }}
                                </p>
                            @endif
                        </article>
                    @empty
                        <div class="rounded-xl border-2 border-dashed border-gray-300/80 bg-white/50 p-5 text-center dark:border-white/10 dark:bg-transparent">
                            <p class="text-2xl">📭</p>
                            <p class="mt-1 text-xs font-medium text-gray-400">{{ __('dashboard.board.empty') }}</p>
                        </div>
                    @endforelse
                </div>
            </section>
        @endforeach
    </div>
</div>
