@extends('layouts.account')

@section('title', t('orders.order_label', 'Order') . ' ' . $order->order_number . ' — ' . $shopSettings['shop_name'])

@section('robots', 'noindex, nofollow')

@section('account-content')
    @php
        $statusLabels = [
            'pending' => t('orders.status_pending', 'Pending'),
            'confirmed' => t('orders.status_confirmed', 'Confirmed'),
            'in_tailoring' => t('orders.status_in_tailoring', 'In Tailoring'),
            'ready' => t('orders.status_ready', 'Ready for Delivery'),
            'delivered' => t('orders.status_delivered', 'Delivered'),
            'cancelled' => t('orders.status_cancelled', 'Cancelled'),
        ];
        $paymentLabels = [
            'cash_on_delivery' => t('payment.method_cash_on_delivery', 'Cash on Delivery'),
            'bank_transfer' => t('payment.method_bank_transfer', 'Bank Transfer'),
            'hesabpay' => t('payment.method_hesabpay', 'HesabPay'),
        ];
        $paymentStatusLabels = [
            'pending' => t('orders.payment_status_pending', 'Pending'),
            'paid' => t('orders.payment_status_paid', 'Paid'),
            'refunded' => t('orders.payment_status_refunded', 'Refunded'),
        ];
        $measureLabels = [
            'chest' => t('common.measure_chest', 'Chest'),
            'waist' => t('common.measure_waist', 'Waist'),
            'hips' => t('common.measure_hips', 'Hips'),
            'shoulder' => t('common.measure_shoulder', 'Shoulder Width'),
            'sleeve' => t('common.measure_sleeve', 'Sleeve Length'),
            'shirt_length' => t('common.measure_shirt_length', 'Shirt / Kameez Length'),
            'neck' => t('common.measure_neck', 'Neck'),
            'armhole' => t('common.measure_armhole', 'Armhole'),
            'wrist' => t('common.measure_wrist', 'Wrist'),
        ];
    @endphp

    <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
        <div>
            <a href="{{ route('account.orders') }}" class="text-sm text-brand-700 hover:underline">&larr; {{ t('account.back_to_orders', 'Back to orders') }}</a>
            <h1 class="font-display text-2xl font-bold text-brand-900 mt-1">{{ t('orders.order_label', 'Order') }} {{ $order->order_number }}</h1>
            <p class="text-sm text-gray-500">{{ t('orders.placed_on', 'Placed') }} {{ $order->created_at->format('M d, Y \a\t H:i') }}</p>
        </div>
        <span class="px-3 py-1.5 rounded-full text-sm font-bold {{ status_badge_class($order->status) }}">{{ $statusLabels[$order->status] ?? $order->statusLabel() }}</span>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-6 mb-6">
        <h2 class="font-semibold text-gray-900 mb-3">{{ t('orders.items_heading', 'Items') }}</h2>
        <div class="divide-y divide-gray-100">
            @foreach($order->items as $item)
                <div class="py-3">
                    <div class="flex justify-between gap-4">
                        <div>
                            <p class="font-medium text-gray-900">{{ $item->product_name }}</p>
                            <p class="text-sm text-gray-500">
                                @if($item->size) {{ t('common.size', 'Size') }}: {{ $item->size }} &middot; @endif
                                {{ t('cart.qty', 'Qty') }}: {{ $item->quantity }}
                                @if($item->type === 'custom') &middot; {{ t('product.made_to_measure', 'Made to measure') }} @endif
                            </p>
                        </div>
                        <p class="font-semibold shrink-0">{{ money($item->line_total) }}</p>
                    </div>
                    @if(!empty($item->measurements))
                        <div class="mt-2 text-xs text-gray-600 bg-gray-50 rounded-lg p-2">
                            <span class="font-medium">{{ t('orders.measurements_label', 'Measurements:') }}</span>
                            @foreach($item->measurements as $field => $value)
                                {{ $measureLabels[$field] ?? (\App\Models\MeasurementProfile::fields()[$field] ?? $field) }}: {{ $value }}"{{ !$loop->last ? ' ·' : '' }}
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>
        <div class="border-t border-gray-200 mt-4 pt-4 text-sm space-y-1">
            <div class="flex justify-between text-gray-600"><span>{{ t('common.subtotal', 'Subtotal') }}</span><span>{{ money($order->subtotal) }}</span></div>
            <div class="flex justify-between text-gray-600"><span>{{ t('common.delivery', 'Delivery') }}</span><span>{{ $order->delivery_fee > 0 ? money($order->delivery_fee) : t('common.free', 'Free') }}</span></div>
            <div class="flex justify-between font-bold text-gray-900"><span>{{ t('common.total', 'Total') }}</span><span>{{ money($order->total) }}</span></div>
            <div class="flex justify-between text-gray-600 pt-1"><span>{{ t('orders.payment', 'Payment') }}</span><span>{{ $paymentLabels[$order->payment_method] ?? payment_method_label($order->payment_method) }} ({{ $paymentStatusLabels[$order->payment_status] ?? ucfirst($order->payment_status) }})</span></div>
        </div>
    </div>

    <div class="bg-white border border-gray-200 rounded-xl p-6">
        <h2 class="font-semibold text-gray-900 mb-3">{{ t('orders.status_history', 'Status History') }}</h2>
        @if($order->statusHistories->isEmpty())
            <p class="text-sm text-gray-500">{{ t('orders.no_updates', 'No updates yet.') }}</p>
        @else
            <ul class="text-sm text-gray-600 space-y-2">
                @foreach($order->statusHistories()->oldest()->get() as $history)
                    <li class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-brand-600 shrink-0"></span>
                        <span>{{ $history->created_at->format('M d, Y H:i') }} — {{ $statusLabels[$history->status] ?? $history->status }}</span>
                    </li>
                @endforeach
            </ul>
        @endif
    </div>
@endsection
