@extends('layouts.admin')

@section('title', t('admin.tailor.requests_title', 'Custom Order Requests'))

@section('content')
    @php
        $statusLabels = [
            'new' => t('custom_orders.status_new', 'New'),
            'assigned' => t('custom_orders.status_assigned', 'Assigned'),
            'in_progress' => t('custom_orders.status_in_progress', 'In Progress'),
            'converted' => t('custom_orders.status_converted', 'Converted to Order'),
            'closed' => t('custom_orders.status_closed', 'Closed'),
        ];
    @endphp

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <a href="{{ route('admin.tailor.dashboard') }}" class="text-sm font-semibold text-brand-500 hover:text-brand-700">{{ t('admin.nav.dashboard', '← Back to dashboard') }}</a>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-brand-100 bg-white shadow-sm">
        <table class="w-full min-w-[820px] text-sm">
            <thead>
                <tr class="border-b border-brand-100 bg-brand-50 text-left text-xs font-semibold uppercase tracking-wide text-brand-500">
                    <th class="px-4 py-3">{{ t('admin.custom_orders.request', 'Request') }}</th>
                    <th class="px-4 py-3">{{ t('admin.common.customer', 'Customer') }}</th>
                    <th class="px-4 py-3">{{ t('admin.common.date', 'Date') }}</th>
                    <th class="px-4 py-3">{{ t('admin.common.status', 'Status') }}</th>
                    <th class="px-4 py-3">{{ t('admin.custom_orders.description', 'Description') }}</th>
                    <th class="px-4 py-3 text-right">{{ t('admin.common.actions', 'Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse ($requests as $requestRecord)
                    <tr class="transition hover:bg-brand-50/50">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.custom-orders.show', $requestRecord) }}" class="font-semibold text-teal-700 hover:underline">#{{ $requestRecord->id }}</a>
                            @if ($requestRecord->convertedOrder)
                                <p class="text-xs text-brand-400">→ {{ $requestRecord->convertedOrder->order_number }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <p class="font-medium text-brand-900">{{ $requestRecord->customer_name }}</p>
                            <p class="text-xs text-brand-400">{{ $requestRecord->customer_phone }}</p>
                        </td>
                        <td class="px-4 py-3 text-brand-600">{{ $requestRecord->created_at->locale(app()->getLocale())->translatedFormat('M j, Y') }}</td>
                        <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ status_badge_class($requestRecord->status) }}">{{ $statusLabels[$requestRecord->status] ?? $requestRecord->status }}</span></td>
                        <td class="px-4 py-3"><span class="truncate block max-w-[220px]">{{ $requestRecord->request_description }}</span></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.custom-orders.show', $requestRecord) }}" class="rounded-lg border border-brand-200 px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:bg-brand-50">{{ t('admin.common.view', 'View') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-brand-400">{{ t('admin.tailor.no_requests', 'No custom order requests yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $requests->links() }}</div>
@endsection
