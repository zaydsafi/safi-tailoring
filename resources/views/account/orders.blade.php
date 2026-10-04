@extends('layouts.account')

@section('title', t('seo.account_orders_title', 'My Orders') . ' — ' . $shopSettings['shop_name'])

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
    @endphp

    <h1 class="font-display text-2xl font-bold text-brand-900 mb-6">{{ t('account.my_orders', 'My Orders') }}</h1>

    @if($orders->isEmpty())
        <div class="text-center py-16 bg-gray-50 rounded-xl border border-dashed border-gray-300">
            <p class="text-gray-500 mb-4">{{ t('account.no_orders', 'You have not placed any orders yet.') }}</p>
            <a href="{{ route('shop.index') }}" class="inline-block bg-brand-800 text-white font-medium px-6 py-3 rounded-lg hover:bg-brand-700">{{ t('account.start_shopping', 'Start Shopping') }}</a>
        </div>
    @else
        <div class="space-y-3">
            @foreach($orders as $order)
                <a href="{{ route('account.orders.show', $order) }}" class="block bg-white border border-gray-200 rounded-xl p-4 hover:border-brand-400 transition-colors">
                    <div class="flex flex-wrap items-center justify-between gap-3">
                        <div>
                            <p class="font-semibold text-gray-900">{{ $order->order_number }}</p>
                            <p class="text-sm text-gray-500">{{ $order->created_at->format('M d, Y') }} &middot; {{ $order->items_count }} {{ $order->items_count === 1 ? t('account.item', 'item') : t('account.items', 'items') }}</p>
                        </div>
                        <div class="flex items-center gap-4">
                            <span class="font-bold text-brand-800">{{ money($order->total) }}</span>
                            <span class="px-3 py-1 rounded-full text-xs font-bold {{ status_badge_class($order->status) }}">{{ $statusLabels[$order->status] ?? $order->statusLabel() }}</span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>

        <div class="mt-6">{{ $orders->links() }}</div>
    @endif
@endsection
