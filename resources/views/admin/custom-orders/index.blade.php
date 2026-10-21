@extends('layouts.admin')

@section('title', t('admin.custom_orders.title', 'Custom Order Requests'))

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
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="{{ t('admin.custom_orders.search_placeholder', 'Name, phone, email or description…') }}"
                   class="w-64 rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
            <select name="status" class="rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                <option value="">{{ t('admin.custom_orders.all_statuses', 'All statuses') }}</option>
                @foreach ($statusLabels as $value => $label)
                    <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-xl bg-brand-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-800">{{ t('admin.common.filter', 'Filter') }}</button>
            @if (request()->filled('q') || request()->filled('status'))
                <a href="{{ route('admin.custom-orders.index') }}" class="text-sm font-semibold text-brand-500 hover:text-brand-700">{{ t('admin.custom_orders.clear', 'Clear') }}</a>
            @endif
        </form>
        <a href="{{ route('admin.custom-orders.create') }}"
           class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            {{ t('admin.custom_orders.create_for_customer', 'Create Order for Customer') }}
        </a>
        @if(auth()->check() && auth()->user()->isTailor())
            <a href="{{ route('admin.tailor.dashboard') }}"
               class="inline-flex items-center gap-2 rounded-xl border border-brand-200 px-4 py-2.5 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M16.862 4.487l1.687-1.688a1.875 1.875 0 112.652 2.652L10.582 16.58a4.5 4.5 0 01-1.897 1.132l-2.687.8.8-2.685a4.5 4.5 0 011.132-1.897L13.114 9.5.418 12.62a4.5 4.5 0 01-1.132-2.652L16.862 4.487z"/></svg>
                {{ t('admin.custom_orders.tailor_view', 'Tailor view') }}
            </a>
        @endif
    </div>

    <div class="overflow-x-auto rounded-2xl border border-brand-100 bg-white shadow-sm">
        <table class="w-full min-w-[880px] text-sm">
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
                        <td class="px-4 py-3">
                            <p class="text-brand-600">{{ $requestRecord->created_at->locale(app()->getLocale())->translatedFormat('M j, Y') }}</p>
                            @if ($requestRecord->user)
                                <p class="text-xs text-brand-400">{{ t('admin.custom_orders.registered', 'Registered') }}</p>
                            @else
                                <p class="text-xs text-brand-400">{{ t('admin.custom_orders.guest', 'Guest') }}</p>
                            @endif
                        </td>
                        <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ status_badge_class($requestRecord->status) }}">{{ $statusLabels[$requestRecord->status] ?? $requestRecord->status }}</span></td>
                        <td class="px-4 py-3"><span class="truncate block max-w-[220px]">{{ $requestRecord->request_description }}</span></td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.custom-orders.show', $requestRecord) }}" class="rounded-lg border border-brand-200 px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:bg-brand-50">{{ t('admin.common.view', 'View') }}</a>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-brand-400">{{ t('admin.custom_orders.no_requests', 'No custom order requests yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $requests->links() }}</div>
@endsection
