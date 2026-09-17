<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <style>
        @font-face {
            font-family: 'Amiri';
            src: url('{{ public_path('fonts/Amiri-Regular.ttf') }}') format('truetype');
            font-weight: normal;
        }
        @font-face {
            font-family: 'Amiri';
            src: url('{{ public_path('fonts/Amiri-Bold.ttf') }}') format('truetype');
            font-weight: bold;
        }
        body {
            font-family: 'Amiri', serif;
            direction: rtl;
            color: #111;
        }
        h1 {
            text-align: center;
            font-size: 24px;
        }
        .meta {
            margin-bottom: 16px;
            font-size: 14px;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            font-size: 13px;
        }
        th, td {
            border: 1px solid #444;
            padding: 6px 8px;
            text-align: right;
        }
        th {
            background: #eee;
            font-weight: bold;
        }
        .totals {
            margin-top: 12px;
            font-size: 14px;
        }
        .totals div {
            margin: 2px 0;
        }
    </style>
</head>
<body>
    <h1>{{ __('invoices.pdf.title') }}</h1>

    <div class="meta">
        <div>{{ __('invoices.pdf.invoice_number') }}: {{ $invoice->invoice_number }}</div>
        <div>{{ __('invoices.pdf.order_number') }}: {{ $invoice->repairOrder->order_number }}</div>
        <div>{{ __('invoices.pdf.customer') }}: {{ $invoice->repairOrder->customer->name }} — {{ $invoice->repairOrder->customer->phone }}</div>
        <div>{{ __('invoices.pdf.issued_at') }}: {{ $invoice->issued_at?->format('Y-m-d') }}</div>
    </div>

    <table>
        <thead>
            <tr>
                <th>{{ __('invoices.pdf.description') }}</th>
                <th>{{ __('invoices.pdf.type') }}</th>
                <th>{{ __('invoices.pdf.quantity') }}</th>
                <th>{{ __('invoices.pdf.unit_price', ['currency' => __('invoices.pdf.currency')]) }}</th>
                <th>{{ __('invoices.pdf.total', ['currency' => __('invoices.pdf.currency')]) }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($invoice->items as $item)
                <tr>
                    <td>{{ $item->description }}</td>
                    <td>{{ __('invoices.types.'.$item->type) }}</td>
                    <td>{{ $item->quantity }}</td>
                    <td>{{ $item->unit_price }}</td>
                    <td>{{ $item->total }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <div class="totals">
        <div>{{ __('invoices.pdf.subtotal') }}: {{ $invoice->subtotal }} {{ __('invoices.pdf.currency') }}</div>
        <div>{{ __('invoices.pdf.discount') }}: {{ $invoice->discount }} {{ __('invoices.pdf.currency') }}</div>
        <div><strong>{{ __('invoices.pdf.grand_total') }}: {{ $invoice->total }} {{ __('invoices.pdf.currency') }}</strong></div>
        <div>{{ __('invoices.pdf.paid') }}: {{ $invoice->paid_total }} {{ __('invoices.pdf.currency') }}</div>
        <div>{{ __('invoices.pdf.remaining') }}: {{ $invoice->remaining_total }} {{ __('invoices.pdf.currency') }}</div>
    </div>
</body>
</html>
