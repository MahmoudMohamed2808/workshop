<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>فاتورة بيع — {{ $order->order_number }}</title>
    <style>
        * { box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Arial, sans-serif;
            direction: rtl;
            color: #111827;
            margin: 0;
            padding: 16px;
            background: #f3f4f6;
        }
        .sheet {
            width: 148mm;
            margin: 0 auto;
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
            padding: 16px 18px;
        }
        .shop { text-align: center; border-bottom: 2px solid #111827; padding-bottom: 10px; margin-bottom: 12px; }
        .shop h1 { font-size: 20px; margin: 0 0 4px; }
        .shop .sub { font-size: 12px; color: #6b7280; }
        .order-line { display: flex; justify-content: space-between; align-items: center; margin-bottom: 12px; font-size: 13px; }
        .order-line .num { font-size: 16px; font-weight: bold; }
        .badge {
            display: inline-block; padding: 3px 12px; border-radius: 999px;
            font-size: 12px; font-weight: bold; background: #eef2ff; border: 1px solid #c7d2fe;
        }
        .badge-green { background: #dcfce7; border-color: #86efac; color: #15803d; }
        .badge-yellow { background: #fef9c3; border-color: #fde047; color: #a16207; }
        .badge-red { background: #fee2e2; border-color: #fca5a5; color: #b91c1c; }
        .empty { text-align: center; font-size: 12px; color: #6b7280; padding: 8px 0; }
        .grid { display: flex; gap: 10px; margin-bottom: 12px; }
        .box { flex: 1; border: 1px solid #e5e7eb; border-radius: 6px; padding: 8px 10px; font-size: 12px; line-height: 1.8; }
        .box h3 { font-size: 12px; margin: 0 0 4px; color: #6b7280; font-weight: bold; }
        table { width: 100%; border-collapse: collapse; font-size: 12px; margin-top: 6px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: right; }
        th { background: #f9fafb; font-size: 12px; }
        td.n, th.n { text-align: center; white-space: nowrap; }
        .section-title { font-size: 13px; font-weight: bold; margin: 14px 0 4px; }
        .totals { margin-top: 12px; border: 1px solid #e5e7eb; border-radius: 6px; padding: 10px 12px; font-size: 13px; line-height: 2; }
        .totals .row { display: flex; justify-content: space-between; }
        .totals .grand { font-size: 16px; font-weight: bold; border-top: 1px dashed #d1d5db; margin-top: 6px; padding-top: 6px; }
        .totals .remain { color: #b91c1c; font-weight: bold; }
        .totals .paid { color: #15803d; }
        .notes { font-size: 11px; color: #4b5563; margin-top: 10px; line-height: 1.9; }
        .footer { margin-top: 16px; font-size: 12px; display: flex; justify-content: space-between; }
        .actions { text-align: center; margin: 12px 0; display: flex; gap: 8px; justify-content: center; }
        .actions button {
            background: #111827; color: #fff; border: 0; border-radius: 6px;
            padding: 9px 26px; font-size: 14px; cursor: pointer;
        }
        .actions a { font-size: 13px; color: #4b5563; align-self: center; }
        @media print {
            body { background: #fff; padding: 0; }
            .sheet { border: 0; border-radius: 0; width: auto; }
            .actions { display: none; }
            @page { size: A5; margin: 8mm; }
        }
    </style>
</head>
<body>
    <div class="actions">
        <button onclick="window.print()">🖨 طباعة الفاتورة</button>
    </div>

    @php
        $statusLabel = \App\Filament\Resources\RepairOrders\RepairOrderStatus::label($order->status);
        $paid = (float) ($invoice?->paid_total ?? 0);
        $grand = (float) $order->total_cost;
        $remaining = (float) ($invoice?->remaining_total ?? $grand);
        $paymentBadge = $remaining <= 0
            ? ['label' => 'مدفوعة بالكامل', 'class' => 'badge-green']
            : ($paid > 0
                ? ['label' => 'مدفوعة جزئيًا', 'class' => 'badge-yellow']
                : ['label' => 'غير مدفوعة', 'class' => 'badge-red']);
        $payments = $invoice?->payments?->sortBy('paid_at') ?? collect();
    @endphp

    <div class="sheet">
        <div class="shop">
            <h1>{{ config('app.name', 'مركز الصيانة') }}</h1>
            <div class="sub">فاتورة بيع — تُطبع عند تجهيز الجهاز للتسليم</div>
        </div>

        <div class="order-line">
            <div>
                <div class="num">{{ $order->order_number }}</div>
                <div class="sub">{{ $order->created_at?->format('Y-m-d') }} — فاتورة: {{ $invoice?->invoice_number ?? 'تُصدر مع الطباعة' }}</div>
            </div>
            <span class="badge">{{ $statusLabel }}</span>
            <span class="badge {{ $paymentBadge['class'] }}">{{ $paymentBadge['label'] }}</span>
        </div>

        <div class="grid">
            <div class="box">
                <h3>العميل</h3>
                <div><strong>{{ $order->customer?->name ?? '—' }}</strong></div>
                <div>{{ $order->customer?->phone ?? '' }}</div>
                <div>{{ $order->customer?->address ?? '' }}</div>
            </div>
            <div class="box">
                <h3>الجهاز</h3>
                <div>{{ trim(implode(' ', array_filter([$order->deviceType?->name, $order->brand?->name, $order->deviceModel?->name]))) ?: '—' }}</div>
                <div>سيريال: {{ $order->serial_number ?? '—' }}</div>
                <div>الفني: {{ $order->technician?->name ?? '—' }}</div>
            </div>
        </div>

        <div class="section-title">بنود الفاتورة</div>
        <table>
            <thead>
                <tr><th>البيان</th><th class="n">الكمية</th><th class="n">السعر</th><th class="n">الإجمالي</th></tr>
            </thead>
            <tbody>
                @foreach ($order->orderProblems as $row)
                    <tr>
                        <td>{{ $row->problem?->name ?? 'عطل' }}{{ $row->notes ? ' — '.$row->notes : '' }}</td>
                        <td class="n">1</td>
                        <td class="n">{{ number_format((float) $row->cost, 2) }}</td>
                        <td class="n">{{ number_format((float) $row->cost, 2) }}</td>
                    </tr>
                @endforeach
                @foreach ($order->orderParts as $row)
                    <tr>
                        <td>{{ $row->sparePart?->name ?? 'قطعة غيار' }}</td>
                        <td class="n">{{ $row->quantity }}</td>
                        <td class="n">{{ number_format((float) $row->unit_price, 2) }}</td>
                        <td class="n">{{ number_format((float) $row->quantity * (float) $row->unit_price, 2) }}</td>
                    </tr>
                @endforeach
                @if ((float) $order->labor_cost > 0)
                    <tr>
                        <td>مصنعية</td>
                        <td class="n">1</td>
                        <td class="n">{{ number_format((float) $order->labor_cost, 2) }}</td>
                        <td class="n">{{ number_format((float) $order->labor_cost, 2) }}</td>
                    </tr>
                @endif
                @if ((float) $order->additional_cost > 0)
                    <tr>
                        <td>تكاليف إضافية</td>
                        <td class="n">1</td>
                        <td class="n">{{ number_format((float) $order->additional_cost, 2) }}</td>
                        <td class="n">{{ number_format((float) $order->additional_cost, 2) }}</td>
                    </tr>
                @endif
                @if ((float) $order->discount > 0)
                    <tr>
                        <td>خصم</td>
                        <td class="n">—</td>
                        <td class="n">—</td>
                        <td class="n">− {{ number_format((float) $order->discount, 2) }}</td>
                    </tr>
                @endif
                @if ($order->orderProblems->isEmpty() && $order->orderParts->isEmpty() && (float) $order->labor_cost <= 0 && (float) $order->additional_cost <= 0)
                    <tr><td colspan="4" style="text-align: center">لا توجد بنود مسجلة</td></tr>
                @endif
            </tbody>
        </table>

        <div class="totals">
            <div class="row"><span>الإجمالي</span><span>{{ number_format($grand, 2) }} ج.م</span></div>
            <div class="row paid"><span>المدفوع</span><span>{{ number_format($paid, 2) }} ج.م</span></div>
            <div class="row grand"><span>المتبقي عند الاستلام</span><span class="remain">{{ number_format($remaining, 2) }} ج.م</span></div>
        </div>

        <div class="section-title">المدفوعات</div>
        @if ($payments->isNotEmpty())
            <table>
                <thead>
                    <tr><th class="n">التاريخ</th><th class="n">المبلغ</th><th class="n">طريقة الدفع</th><th>رقم المرجع</th></tr>
                </thead>
                <tbody>
                    @foreach ($payments as $payment)
                        <tr>
                            <td class="n">{{ $payment->paid_at?->format('Y-m-d H:i') ?? '—' }}</td>
                            <td class="n">{{ number_format((float) $payment->amount, 2) }}</td>
                            <td class="n">{{ match ($payment->method) { 'cash' => 'نقدي', 'vodafone_cash' => 'فودافون كاش', 'bank_transfer' => 'تحويل بنكي', default => $payment->method } }}</td>
                            <td>{{ $payment->reference_number ?? '—' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        @else
            <div class="empty">لا توجد مدفوعات مسجلة</div>
        @endif

        @if (filled($order->technician_diagnosis) || filled($order->customer_complaint))
            <div class="notes">
                @if (filled($order->customer_complaint))
                    <div><strong>شكوى العميل:</strong> {{ $order->customer_complaint }}</div>
                @endif
                @if (filled($order->technician_diagnosis))
                    <div><strong>التشخيص:</strong> {{ $order->technician_diagnosis }}</div>
                @endif
            </div>
        @endif

        <div class="footer">
            <span>توقيع العميل: …………………</span>
            <span>توقيع الفني: …………………</span>
        </div>
    </div>
</body>
</html>
