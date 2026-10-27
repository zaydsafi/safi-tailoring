@extends('layouts.admin')

@section('title', t('admin.tailor.dashboard_title', 'Tailor Dashboard'))

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

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2 space-y-6">
            <div data-reveal class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                <div class="mb-4 flex flex-col items-start gap-1 sm:flex-row sm:items-center sm:justify-between sm:gap-x-4">
                    <h2 class="text-base font-bold text-brand-900">{{ t('admin.tailor.pending_requests', 'Pending Custom Order Requests') }}</h2>
                    <a href="{{ route('admin.tailor.requests') }}" class="text-sm font-semibold text-teal-700 hover:text-teal-800">{{ t('admin.common.view_all', 'View all') }}</a>
                </div>

                @if ($pendingRequests->isEmpty())
                    <p class="text-sm text-brand-400 py-4">{{ t('admin.tailor.no_pending_requests', 'No pending custom order requests.') }}</p>
                @else
                    <div class="space-y-3">
                        @foreach ($pendingRequests as $requestRecord)
                            <a href="{{ route('admin.custom-orders.show', $requestRecord) }}" class="block rounded-xl border border-brand-100 p-4 transition duration-200 hover:-translate-y-0.5 hover:border-teal-300 hover:shadow-md">
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="font-semibold text-brand-900">{{ $requestRecord->customer_name }}</p>
                                        <p class="text-sm text-brand-600">{{ $requestRecord->request_description }}</p>
                                    </div>
                                    <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ status_badge_class($requestRecord->status) }}">{{ $statusLabels[$requestRecord->status] ?? $requestRecord->status }}</span>
                                </div>
                                <div class="mt-2 flex flex-wrap gap-3 text-xs text-brand-500">
                                    <span>{{ t('admin.common.phone', 'Phone') }}: {{ $requestRecord->customer_phone }}</span>
                                    <span>{{ t('admin.common.date', 'Received') }}: {{ $requestRecord->created_at->locale(app()->getLocale())->translatedFormat('M j, H:i') }}</span>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>

            <div data-reveal class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-bold text-brand-900">{{ t('admin.tailor.recently_converted', 'Recently Converted to Orders') }}</h2>
                @if ($recentlyConverted->isEmpty())
                    <p class="text-sm text-brand-400 py-4">{{ t('admin.tailor.no_recent_conversions', 'No recently converted requests.') }}</p>
                @else
                    <div class="space-y-3">
                        @foreach ($recentlyConverted as $requestRecord)
                            @if ($requestRecord->convertedOrder)
                                <a href="{{ route('admin.orders.show', $requestRecord->convertedOrder) }}" class="block rounded-xl border border-brand-100 p-4 transition duration-200 hover:-translate-y-0.5 hover:border-teal-300 hover:shadow-md">
                                    <div class="flex items-start justify-between gap-3">
                                        <div>
                                            <p class="font-semibold text-brand-900">{{ $requestRecord->customer_name }}</p>
                                            <p class="text-sm text-brand-600">{{ $requestRecord->request_description }}</p>
                                        </div>
                                        <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">{{ t('admin.custom_orders.converted', 'Converted') }}</span>
                                    </div>
                                    <div class="mt-2 flex flex-wrap gap-3 text-xs text-brand-500">
                                        <span>{{ t('admin.orders.order_number', 'Order') }}: {{ $requestRecord->convertedOrder->order_number }}</span>
                                        <span>{{ t('admin.common.total', 'Total') }}: {{ money($requestRecord->convertedOrder->total) }}</span>
                                    </div>
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="space-y-6">
            <div data-reveal class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-bold text-brand-900">{{ t('admin.tailor.customers', 'Customers') }}</h2>
                @if ($customers->isEmpty())
                    <p class="text-sm text-brand-400 py-4">{{ t('admin.tailor.no_customers', 'No customers yet.') }}</p>
                @else
                    <div class="space-y-3">
                        @foreach ($customers as $customer)
                            <div class="rounded-xl border border-brand-100 p-3 transition duration-200 hover:-translate-y-0.5 hover:border-teal-300 hover:shadow-md">
                                <p class="font-semibold text-brand-900 text-sm">{{ $customer->name }}</p>
                                <p class="text-xs text-brand-600">{{ $customer->phone }}</p>
                                @if ($customer->measurement_profiles_count > 0)
                                    <span class="inline-block mt-1 rounded-full bg-teal-50 px-2 py-0.5 text-xs font-semibold text-teal-700">{{ t('admin.tailor.has_measurements', 'Has measurements') }}</span>
                                @endif
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
