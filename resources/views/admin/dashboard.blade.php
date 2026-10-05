@extends('layouts.admin')

@section('title', t('admin.dashboard.title', 'Dashboard'))

@section('content')
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

    {{-- Stats --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        @php
            $cards = [
                ['label' => t('admin.dashboard.revenue_paid', 'Revenue (Paid)'), 'value' => money($revenue), 'icon' => 'M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label' => t('admin.dashboard.orders_today', 'Orders Today'), 'value' => $todayOrders, 'icon' => 'M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 012.25-2.25h13.5A2.25 2.25 0 0121 7.5v11.25m-18 0A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75m-18 0v-7.5A2.25 2.25 0 015.25 9h13.5A2.25 2.25 0 0121 11.25v7.5'],
                ['label' => t('admin.dashboard.pending_orders', 'Pending Orders'), 'value' => $pendingOrders, 'icon' => 'M12 6v6h4.5m4.5 0a9 9 0 11-18 0 9 9 0 0118 0z'],
                ['label' => t('admin.dashboard.products', 'Products'), 'value' => $totalProducts, 'icon' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5m16.5 0h-16.5m16.5 0H3.375c-.621 0-1.125-.504-1.125-1.125V4.875c0-.621.504-1.125 1.125-1.125H20.625c.621 0 1.125.504 1.125 1.125v1.5c0 .621-.504 1.125-1.125 1.125z'],
            ];
        @endphp
        @foreach ($cards as $card)
            <div class="rounded-2xl border border-brand-100 bg-white p-5 shadow-sm">
                <div class="flex items-center justify-between">
                    <p class="text-sm font-medium text-brand-500">{{ $card['label'] }}</p>
                    <svg class="h-6 w-6 text-teal-700/40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="{{ $card['icon'] }}"/></svg>
                </div>
                <p class="mt-2 text-2xl font-bold text-brand-900">{{ $card['value'] }}</p>
            </div>
        @endforeach
    </div>

    <div class="mt-4 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-brand-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-brand-500">{{ t('admin.dashboard.customers', 'Customers') }}</p>
            <p class="mt-2 text-2xl font-bold text-brand-900">{{ $totalCustomers }}</p>
        </div>
        <a href="{{ route('admin.messages.index') }}" class="rounded-2xl border border-brand-100 bg-white p-5 shadow-sm transition hover:border-teal-300">
            <p class="text-sm font-medium text-brand-500">{{ t('admin.dashboard.unread_messages', 'Unread Messages') }}</p>
            <p class="mt-2 text-2xl font-bold {{ $unreadMessages > 0 ? 'text-gold-600' : 'text-brand-900' }}">{{ $unreadMessages }}</p>
        </a>
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-3">
        {{-- Revenue & orders trend --}}
        <div class="rounded-2xl border border-brand-100 bg-white p-5 shadow-sm xl:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-base font-bold text-brand-900">{{ t('admin.dashboard.revenue_orders', 'Revenue & Orders') }}</h2>
                <span class="text-xs font-medium text-brand-400">{{ t('admin.dashboard.trend_note', 'Last 14 days · cancelled orders excluded') }}</span>
            </div>
            <div class="relative h-72">
                <canvas id="revenueChart"
                        data-labels="{{ json_encode($chart['labels']) }}"
                        data-revenue="{{ json_encode($chart['revenue']) }}"
                        data-orders="{{ json_encode($chart['orders']) }}"></canvas>
            </div>
        </div>

        {{-- Status breakdown --}}
        <div class="rounded-2xl border border-brand-100 bg-white p-5 shadow-sm">
            <h2 class="mb-4 text-base font-bold text-brand-900">{{ t('admin.dashboard.orders_by_status', 'Orders by Status') }}</h2>
            <div class="relative h-72">
                <canvas id="statusChart"
                        data-labels="{{ json_encode($chart['statusLabels']) }}"
                        data-counts="{{ json_encode($chart['statusCounts']) }}"></canvas>
            </div>
        </div>
    </div>

    <div class="mt-8 grid gap-6 xl:grid-cols-3">
        {{-- Recent orders --}}
        <div class="min-w-0 xl:col-span-2">
            <div class="mb-4 flex items-center justify-between">
                <h2 class="text-base font-bold text-brand-900">{{ t('admin.dashboard.recent_orders', 'Recent Orders') }}</h2>
                <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-800">{{ t('admin.dashboard.view_all', 'View all') }}</a>
            </div>
            <div class="overflow-x-auto rounded-2xl border border-brand-100 bg-white shadow-sm">
                <table class="w-full min-w-[560px] text-sm">
                    <thead>
                        <tr class="border-b border-brand-100 bg-brand-50 text-left text-xs font-semibold uppercase tracking-wide text-brand-500">
                            <th class="px-4 py-3">{{ t('admin.dashboard.order', 'Order') }}</th>
                            <th class="px-4 py-3">{{ t('admin.common.customer', 'Customer') }}</th>
                            <th class="px-4 py-3">{{ t('admin.dashboard.items', 'Items') }}</th>
                            <th class="px-4 py-3">{{ t('admin.common.total', 'Total') }}</th>
                            <th class="px-4 py-3">{{ t('admin.common.status', 'Status') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-50">
                        @forelse ($recentOrders as $order)
                            <tr class="transition hover:bg-brand-50/50">
                                <td class="px-4 py-3">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-teal-700 hover:underline">{{ $order->order_number }}</a>
                                    <p class="text-xs text-brand-400">{{ $order->created_at->locale(app()->getLocale())->translatedFormat('M j, H:i') }}</p>
                                </td>
                                <td class="px-4 py-3">{{ $order->customer_name }}</td>
                                <td class="px-4 py-3">{{ $order->items_count }}</td>
                                <td class="px-4 py-3 font-semibold">{{ money($order->total) }}</td>
                                <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ status_badge_class($order->status) }}">{{ $statusLabels[$order->status] ?? $order->statusLabel() }}</span></td>
                            </tr>
                        @empty
                            <tr><td colspan="5" class="px-4 py-10 text-center text-brand-400">{{ t('admin.dashboard.no_orders', 'No orders yet.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Low stock --}}
        <div>
            <h2 class="mb-4 text-base font-bold text-brand-900">{{ t('admin.dashboard.low_stock', 'Low Stock Alerts') }}</h2>
            <div class="rounded-2xl border border-brand-100 bg-white p-4 shadow-sm">
                @forelse ($lowStock as $product)
                    <div class="flex items-center justify-between border-b border-brand-50 py-3 last:border-0">
                        <div class="min-w-0">
                            <a href="{{ route('admin.products.edit', $product) }}" class="block truncate text-sm font-semibold text-brand-900 hover:text-teal-700">{{ $product->name }}</a>
                            <p class="text-xs text-brand-400">{{ $product->category->name ?? t('admin.dashboard.uncategorized', 'Uncategorized') }}</p>
                        </div>
                        <span class="ml-3 shrink-0 rounded-full px-2.5 py-1 text-xs font-bold {{ $product->stock === 0 ? 'bg-red-100 text-red-700' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $product->stock === 0 ? t('admin.dashboard.out_of_stock', 'Out') : t('admin.dashboard.stock_left', ':count left', ['count' => $product->stock]) }}
                        </span>
                    </div>
                @empty
                    <p class="py-6 text-center text-sm text-brand-400">{{ t('admin.dashboard.all_stocked', 'All products well stocked.') }}</p>
                @endforelse
            </div>
        </div>
    </div>
@endsection
