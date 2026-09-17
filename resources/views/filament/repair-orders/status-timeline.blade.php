@php
    use App\Filament\Resources\RepairOrders\RepairOrderStatus;

    $flow = RepairOrderStatus::flow();
    $currentIndex = array_search($record->status, $flow, true);
    $isTerminalException = ! is_int($currentIndex);
@endphp

<div dir="{{ app()->getLocale() === 'ar' ? 'rtl' : 'ltr' }}">
    @if ($isTerminalException)
        <div class="rounded-lg bg-danger-50 px-4 py-3 text-sm font-medium text-danger-700 dark:bg-danger-500/10 dark:text-danger-300">
            {{ RepairOrderStatus::label($record->status) }}
            @if (filled($record->rejection_reason))
                — {{ $record->rejection_reason }}
            @endif
        </div>
    @else
        <ol class="flex flex-wrap items-center gap-y-3">
            @foreach ($flow as $index => $status)
                @php
                    $isDone = $index < $currentIndex;
                    $isCurrent = $index === $currentIndex;
                @endphp
                <li class="flex items-center">
                    <span @class([
                        'flex items-center gap-2 rounded-full px-3 py-1.5 text-xs font-medium',
                        'bg-success-500 text-white' => $isDone,
                        'bg-primary-500 text-white ring-2 ring-primary-500/30' => $isCurrent,
                        'bg-gray-100 text-gray-500 dark:bg-white/10 dark:text-gray-400' => ! $isDone && ! $isCurrent,
                    ])>
                        @if ($isDone)
                            <x-heroicon-s-check class="h-3.5 w-3.5" />
                        @elseif ($isCurrent)
                            <span class="h-2 w-2 animate-pulse rounded-full bg-white"></span>
                        @else
                            <span class="flex h-3.5 w-3.5 items-center justify-center rounded-full border border-current text-[10px]">{{ $index + 1 }}</span>
                        @endif
                        {{ RepairOrderStatus::label($status) }}
                    </span>
                    @if ($index < count($flow) - 1)
                        <span class="mx-1 h-px w-4 bg-gray-300 sm:w-6 dark:bg-white/20"></span>
                    @endif
                </li>
            @endforeach
        </ol>
    @endif
</div>
