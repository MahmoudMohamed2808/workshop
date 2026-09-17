<div class="flex flex-col items-center gap-4 py-2" dir="ltr">
    <div class="rounded-lg border border-gray-200 bg-white p-4 dark:border-white/10 dark:bg-white/5">
        {!! $svg !!}
    </div>
    <div class="text-center">
        <p class="text-sm font-semibold">{{ $order->order_number }}</p>
        <p class="text-xs text-gray-500">{{ $order->customer?->name }} — {{ $order->customer?->phone }}</p>
    </div>
    <button type="button" onclick="window.print()" class="fi-btn fi-btn-color-gray">
        {{ __('resources.repair_orders.actions.print_qr_code.label') }}
    </button>
</div>
