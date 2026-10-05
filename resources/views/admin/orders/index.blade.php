@extends('layouts.admin')

@section('title', t('admin.orders.title', 'Orders'))

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
        $paymentStatusLabels = [
            'pending' => t('orders.payment_status_pending', 'Pending'),
            'paid' => t('orders.payment_status_paid', 'Paid'),
            'refunded' => t('orders.payment_status_refunded', 'Refunded'),
        ];
    @endphp

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ t('admin.orders.search_placeholder', 'Order #, name or phone…') }}"
                   class="w-64 rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
            <select name="status" class="rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                <option value="">{{ t('admin.orders.all_statuses', 'All statuses') }}</option>
                @foreach ($statuses as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $statusLabels[$value] ?? $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-xl bg-brand-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-800">{{ t('admin.common.filter', 'Filter') }}</button>
            @if (request()->filled('q') || request()->filled('status'))
                <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-brand-500 hover:text-brand-700">{{ t('admin.orders.clear', 'Clear') }}</a>
            @endif
        </form>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-brand-100 bg-white shadow-sm">
        <table class="w-full min-w-[880px] text-sm">
            <thead>
                <tr class="border-b border-brand-100 bg-brand-50 text-left text-xs font-semibold uppercase tracking-wide text-brand-500">
                    <th class="px-4 py-3">{{ t('admin.orders.order', 'Order') }}</th>
                    <th class="px-4 py-3">{{ t('admin.common.customer', 'Customer') }}</th>
                    <th class="px-4 py-3">{{ t('admin.common.date', 'Date') }}</th>
                    <th class="px-4 py-3">{{ t('admin.orders.items', 'Items') }}</th>
                    <th class="px-4 py-3">{{ t('admin.common.total', 'Total') }}</th>
                    <th class="px-4 py-3">{{ t('admin.orders.payment', 'Payment') }}</th>
                    <th class="px-4 py-3">{{ t('admin.common.status', 'Status') }}</th>
                    <th class="px-4 py-3 text-right">{{ t('admin.common.actions', 'Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse ($orders as $order)
                    <tr class="transition hover:bg-brand-50/50">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.orders.show', $order) }}" class="font-semibold text-teal-700 hover:underline">{{ $order->order_number }}</a>
                            <p class="text-xs text-brand-400">{{ payment_method_label($order->payment_method) }}</p>
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-brand-900">{{ $order->customer_name }}</p>
                            <p class="text-xs text-brand-400">{{ $order->customer_phone }}</p>
                        </td>
                        <td class="px-4 py-3 text-brand-600">{{ $order->created_at->locale(app()->getLocale())->translatedFormat('M j, Y') }}</td>
                        <td class="px-4 py-3">{{ $order->items_count }}</td>
                        <td class="px-4 py-3 font-semibold">{{ money($order->total) }}</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ status_badge_class($order->payment_status) }}">{{ $paymentStatusLabels[$order->payment_status] ?? ucfirst($order->payment_status) }}</span></td>
                        <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ status_badge_class($order->status) }}">{{ $statusLabels[$order->status] ?? $order->statusLabel() }}</span></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.orders.show', $order) }}" class="rounded-lg border border-brand-200 px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:bg-brand-50">{{ t('admin.orders.manage', 'Manage') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="8" class="px-4 py-10 text-center text-brand-400">{{ t('admin.orders.no_orders', 'No orders found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $orders->links() }}</div>
@endsection
