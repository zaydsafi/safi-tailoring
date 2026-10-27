<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $order->order_number }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Helvetica, Arial, sans-serif; font-size: 11px; color: #1f2937; }
        .header { border-bottom: 2px solid #0f766e; padding-bottom: 16px; margin-bottom: 24px; }
        .shop-name { font-size: 20px; font-weight: bold; color: #0f766e; }
        .muted { color: #6b7280; }
        table { width: 100%; border-collapse: collapse; }
        th { text-align: left; font-size: 10px; text-transform: uppercase; color: #6b7280; border-bottom: 1px solid #e5e7eb; padding: 6px 4px; }
        td { padding: 6px 4px; border-bottom: 1px solid #f3f4f6; vertical-align: top; }
        .r { text-align: right; }
        .box { border: 1px solid #e5e7eb; border-radius: 6px; padding: 12px; }
        .total-row td { font-weight: bold; font-size: 13px; border-top: 2px solid #9ca3af; border-bottom: none; }
        .footer { margin-top: 28px; border-top: 1px solid #e5e7eb; padding-top: 10px; color: #6b7280; font-size: 10px; }
    </style>
</head>
<body>
    <table class="header">
        <tr>
            <td>
                <div class="shop-name">{{ $shopName }}</div>
                @if ($shopAddress)<div class="muted">{{ $shopAddress }}</div>@endif
                <div class="muted">
                    @if ($shopPhone)Phone: {{ $shopPhone }}@endif
                    @if ($shopPhone && $shopEmail) &nbsp;·&nbsp; @endif
                    @if ($shopEmail)Email: {{ $shopEmail }}@endif
                </div>
            </td>
            <td class="r">
                <div style="font-size:18px;font-weight:bold;">{{ $order->paid_at || $order->payment_status === 'paid' ? 'Receipt' : 'Invoice' }}</div>
                <div style="font-weight:bold;">{{ $order->order_number }}</div>
                <div class="muted">Date: {{ optional($order->ordered_at ?? $order->created_at)->format('F j, Y') }}</div>
                <div class="muted">Status: {{ \App\Models\Order::STATUSES[$order->status] ?? ucfirst((string) $order->status) }}</div>
            </td>
        </tr>
    </table>

    <table style="margin-bottom:20px;">
        <tr>
            <td style="width:50%;border:none;">
                <div class="box">
                    <div style="font-size:10px;text-transform:uppercase;color:#6b7280;margin-bottom:6px;">Billed to</div>
                    <div style="font-weight:bold;">{{ $order->customer_name }}</div>
                    <div>{{ $order->customer_phone }}</div>
                    @if ($order->customer_email)<div>{{ $order->customer_email }}</div>@endif
                    @if ($order->customer_address)<div>{{ $order->customer_address }}</div>@endif
                    @if ($order->customer_city)<div>{{ $order->customer_city }}</div>@endif
                </div>
            </td>
            <td style="width:50%;border:none;">
                <div class="box">
                    <div style="font-size:10px;text-transform:uppercase;color:#6b7280;margin-bottom:6px;">Payment</div>
                    <div style="font-weight:bold;">{{ ucfirst(str_replace('_', ' ', (string) $order->payment_method)) }}</div>
                    <div class="muted">Payment status: {{ ucfirst((string) $order->payment_status) }}</div>
                    @if ($order->payment_transaction_id)
                        <div class="muted">Transaction: {{ $order->payment_transaction_id }}</div>
                    @endif
                </div>
            </td>
        </tr>
    </table>

    <table>
        <thead>
            <tr>
                <th>Item</th>
                <th>Size / Type</th>
                <th class="r">Qty</th>
                <th class="r">Price</th>
                <th class="r">Amount</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($order->items as $item)
                <tr>
                    <td>
                        {{ $item->product_name }}
                        @if (!empty($item->measurements))
                            <div class="muted" style="font-size:9px;">Made to measure:
                                @foreach ($item->measurements as $field => $value)
                                    {{ str_replace('_', ' ', $field) }}: {{ $value }}″{{ ! $loop->last ? ' ·' : '' }}
                                @endforeach
                            </div>
                        @endif
                    </td>
                    <td class="muted">
                        {{ $item->size ? 'Size: '.$item->size : '' }}
                        {{ $item->type === 'custom' ? 'Made to measure' : ($item->type === 'service' ? 'Service' : 'Ready') }}
                    </td>
                    <td class="r">{{ $item->quantity }}</td>
                    <td class="r">{{ number_format((float) $item->price, 2) }}</td>
                    <td class="r">{{ number_format((float) $item->line_total, 2) }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="4" class="r muted">Subtotal</td>
                <td class="r">{{ number_format((float) $order->subtotal, 2) }} {{ $currency }}</td>
            </tr>
            <tr>
                <td colspan="4" class="r muted">Delivery fee</td>
                <td class="r">{{ $order->delivery_fee > 0 ? number_format((float) $order->delivery_fee, 2).' '.$currency : 'Free' }}</td>
            </tr>
            <tr class="total-row">
                <td colspan="4" class="r">Total</td>
                <td class="r">{{ number_format((float) $order->total, 2) }} {{ $currency }}</td>
            </tr>
        </tfoot>
    </table>

    <div class="footer">
        {{ $footerNote }}
        <span style="float:right;">{{ ucfirst(str_replace('_', ' ', (string) $order->payment_method)) }} · {{ $order->order_number }}</span>
    </div>
</body>
</html>
