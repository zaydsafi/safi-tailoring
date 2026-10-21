@extends('layouts.admin')

@section('title', t('admin.custom_orders.title_with_id', 'Custom Order Request #:id', ['id' => $requestRecord->id]))

@section('content')
    @php
        $statusLabels = [
            'new' => t('custom_orders.status_new', 'New'),
            'assigned' => t('custom_orders.status_assigned', 'Assigned'),
            'in_progress' => t('custom_orders.status_in_progress', 'In Progress'),
            'converted' => t('custom_orders.status_converted', 'Converted to Order'),
            'closed' => t('custom_orders.status_closed', 'Closed'),
        ];
        $measurementFields = [
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
        $waLink = \App\Support\WhatsApp::link($requestRecord->customer_phone, t('admin.custom_orders.whatsapp_intro', 'Hello :name! This is :shop about your custom order request #:id.', ['name' => $requestRecord->customer_name, 'shop' => $shopSettings['shop_name'], 'id' => $requestRecord->id]));
    @endphp

    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="rounded-full px-3 py-1.5 text-sm font-semibold {{ status_badge_class($requestRecord->status) }}">{{ $statusLabels[$requestRecord->status] ?? $requestRecord->status }}</span>
            @if ($requestRecord->convertedOrder)
                <a href="{{ route('admin.orders.show', $requestRecord->convertedOrder) }}" class="rounded-full border border-brand-200 px-3 py-1.5 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">
                    {{ t('admin.custom_orders.converted_order', 'Converted Order') }}: {{ $requestRecord->convertedOrder->order_number }}
                </a>
            @endif
        </div>
        <a href="{{ route('admin.custom-orders.index') }}" class="text-sm font-semibold text-brand-500 hover:text-brand-700">{{ t('admin.custom_orders.back_to_requests', '← Back to requests') }}</a>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-bold text-brand-900">{{ t('admin.custom_orders.request_details', 'Request Details') }}</h2>
                <dl class="grid gap-3 sm:grid-cols-2 text-sm">
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-brand-400">{{ t('admin.common.name', 'Name') }}</dt><dd class="mt-0.5 font-medium text-brand-900">{{ $requestRecord->customer_name }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-brand-400">{{ t('admin.common.phone', 'Phone') }}</dt><dd class="mt-0.5 font-medium text-brand-900">{{ $requestRecord->customer_phone }}</dd></div>
                    @if ($requestRecord->customer_email)
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-brand-400">{{ t('admin.common.email', 'Email') }}</dt><dd class="mt-0.5 font-medium text-brand-900">{{ $requestRecord->customer_email }}</dd></div>
                    @endif
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-brand-400">{{ t('admin.common.address', 'Address') }}</dt><dd class="mt-0.5 font-medium text-brand-900">{{ $requestRecord->customer_address }}</dd></div>
                    @if ($requestRecord->customer_city)
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-brand-400">{{ t('admin.custom_orders.city', 'City') }}</dt><dd class="mt-0.5 font-medium text-brand-900">{{ $requestRecord->customer_city }}</dd></div>
                    @endif
                </dl>
            </div>

            <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-bold text-brand-900">{{ t('admin.custom_orders.what_you_need', 'What You Need Made') }}</h2>
                <p class="text-sm text-brand-700">{{ $requestRecord->request_description }}</p>
                @if ($requestRecord->preferred_fabric)
                    <p class="mt-3 text-sm text-brand-600"><span class="font-semibold">{{ t('admin.custom_orders.preferred_fabric', 'Preferred fabric') }}:</span> {{ $requestRecord->preferred_fabric }}</p>
                @endif
                @if ($requestRecord->notes)
                    <div class="mt-3 rounded-lg bg-brand-50 px-3 py-2 text-sm text-brand-700"><span class="font-semibold">{{ t('admin.custom_orders.notes', 'Notes') }}:</span> {{ $requestRecord->notes }}</div>
                @endif
            </div>

            @if (!empty($requestRecord->measurements))
                <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-base font-bold text-brand-900">{{ t('admin.custom_orders.measurements', 'Measurements (inches)') }}</h2>
                    <div class="flex flex-wrap gap-2">
                        @foreach ($requestRecord->measurements as $field => $value)
                            <span class="rounded-lg bg-brand-50 px-3 py-1.5 text-sm font-medium text-brand-700">{{ $measurementFields[$field] ?? ucfirst(str_replace('_', ' ', $field)) }}: {{ $value }}″</span>
                        @endforeach
                    </div>
                </div>
            @endif

            <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-bold text-brand-900">{{ t('admin.common.account', 'Account') }}</h2>
                <p class="text-sm text-brand-700">
                    @if ($requestRecord->user)
                        <a href="{{ route('admin.users.edit', $requestRecord->user) }}" class="font-semibold text-teal-700 hover:underline">{{ $requestRecord->user->name }}</a>
                        <span class="text-brand-400">·</span> {{ t('admin.custom_orders.registered_customer', 'Registered customer') }}
                    @else
                        {{ t('admin.custom_orders.guest_request', 'Guest request') }}
                    @endif
                </p>
            </div>
        </div>

        <div class="space-y-6">
            <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-bold text-brand-900">{{ t('admin.custom_orders.customer', 'Customer') }}</h2>
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-brand-400">{{ t('admin.common.name', 'Name') }}</dt><dd class="mt-0.5 font-medium text-brand-900">{{ $requestRecord->customer_name }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-brand-400">{{ t('admin.common.phone', 'Phone') }}</dt><dd class="mt-0.5 font-medium text-brand-900">{{ $requestRecord->customer_phone }}</dd></div>
                    @if ($requestRecord->customer_email)
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-brand-400">{{ t('admin.common.email', 'Email') }}</dt><dd class="mt-0.5 font-medium text-brand-900">{{ $requestRecord->customer_email }}</dd></div>
                    @endif
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-brand-400">{{ t('admin.common.address', 'Address') }}</dt><dd class="mt-0.5 font-medium text-brand-900">{{ $requestRecord->customer_address }}</dd></div>
                    @if ($requestRecord->customer_city)
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-brand-400">{{ t('admin.custom_orders.city', 'City') }}</dt><dd class="mt-0.5 font-medium text-brand-900">{{ $requestRecord->customer_city }}</dd></div>
                    @endif
                </dl>
                @if ($waLink)
                    <a href="{{ $waLink }}" target="_blank" rel="noopener"
                       class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        {{ t('admin.custom_orders.whatsapp_chat', 'Chat on WhatsApp') }}
                    </a>
                @endif
            </div>                <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-base font-bold text-brand-900">{{ t('admin.custom_orders.update_status', 'Update Status') }}</h2>
                    <form method="POST" action="{{ route('admin.custom-orders.status', $requestRecord) }}" class="space-y-3">
                        @csrf
                        @method('PATCH')
                        <div>
                            <label for="status" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.custom_orders.status', 'Status') }}</label>
                            <select name="status" id="status" class="w-full rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                                @foreach ($statusLabels as $value => $label)
                                    <option value="{{ $value }}" @selected($requestRecord->status === $value)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="flex gap-2">
                            <input type="text" name="assigned_to" value="{{ old('assigned_to', $requestRecord->assigned_to) }}" placeholder="{{ t('admin.custom_orders.assigned_to_placeholder', 'Assigned to (optional)') }}" class="flex-1 rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                            <button type="submit" class="rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">{{ t('admin.common.update', 'Update') }}</button>
                        </div>
                    </form>
                </div>

                @if ($requestRecord->status !== 'converted' && $requestRecord->status !== 'closed')
                    <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                        <h2 class="mb-4 text-base font-bold text-brand-900">{{ t('admin.custom_orders.convert_to_order', 'Convert to Order') }}</h2>
                        <form method="POST" action="{{ route('admin.custom-orders.convert', $requestRecord) }}" class="space-y-4">
                            @csrf
                            @method('POST')
                            <div class="rounded-lg border border-brand-100 p-4">
                                <label class="mb-1 block text-xs font-medium text-brand-600">{{ t('admin.custom_orders.description', 'What to make') }} <span class="text-red-500">*</span></label>
                                <input type="text" name="items[0][description]" value="{{ old('items.0.description', $requestRecord->request_description) }}" required placeholder="e.g. 2-piece suit"
                                       class="w-full rounded-lg border border-brand-200 px-3 py-2 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                            </div>
                            <div class="grid gap-3 sm:grid-cols-2">
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-brand-600">{{ t('admin.custom_orders.price', 'Price (AFN)') }} <span class="text-red-500">*</span></label>
                                    <input type="number" step="0.01" min="0" name="items[0][price]" value="{{ old('items.0.price', 0) }}" required
                                           class="w-full rounded-lg border border-brand-200 px-3 py-2 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                                </div>
                                <div>
                                    <label class="mb-1 block text-xs font-medium text-brand-600">{{ t('admin.common.quantity', 'Quantity') }} <span class="text-red-500">*</span></label>
                                    <input type="number" min="1" name="items[0][quantity]" value="{{ old('items.0.quantity', 1) }}" required
                                           class="w-full rounded-lg border border-brand-200 px-3 py-2 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                                </div>
                            </div>
                            <div>
                                <label class="mb-1 block text-xs font-medium text-brand-600">{{ t('admin.custom_orders.measurements_optional', 'Measurements (inches, optional)') }}</label>
                                <div class="grid gap-3 sm:grid-cols-3">
                                    <div>
                                        <label class="mb-1 block text-[11px] font-medium text-brand-500">{{ $measurementFields['chest'] }}</label>
                                        <input type="number" step="0.5" min="0" name="items[0][measurements][chest]" value="{{ old('items.0.measurements.chest', ($requestRecord->measurements['chest'] ?? null)) }}"
                                               class="w-full rounded-lg border border-brand-200 px-3 py-2 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-[11px] font-medium text-brand-500">{{ $measurementFields['waist'] }}</label>
                                        <input type="number" step="0.5" min="0" name="items[0][measurements][waist]" value="{{ old('items.0.measurements.waist', ($requestRecord->measurements['waist'] ?? null)) }}"
                                               class="w-full rounded-lg border border-brand-200 px-3 py-2 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                                    </div>
                                    <div>
                                        <label class="mb-1 block text-[11px] font-medium text-brand-500">{{ $measurementFields['shirt_length'] }}</label>
                                        <input type="number" step="0.5" min="0" name="items[0][measurements][shirt_length]" value="{{ old('items.0.measurements.shirt_length', ($requestRecord->measurements['shirt_length'] ?? null)) }}"
                                               class="w-full rounded-lg border border-brand-200 px-3 py-2 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                                    </div>
                                </div>
                            </div>
                            <div class="mt-2 text-xs text-brand-500">{{ t('admin.custom_orders.convert_note', 'You can edit the item description, price and quantity before the order is created.') }}</div>
                            <button type="submit" class="w-full rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">
                                {{ t('admin.custom_orders.convert_to_order', 'Convert to Order') }}
                            </button>
                        </form>
                    </div>
                @endif

            @if ($requestRecord->status === 'converted' && $requestRecord->convertedOrder)
                <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-base font-bold text-brand-900">{{ t('admin.custom_orders.converted_to_order', 'Converted to Order') }}</h2>
                    <p class="text-sm text-brand-700">{{ t('admin.custom_orders.converted_note', 'This request has been turned into a real order. You can now track and manage it from the orders panel.') }}</p>
                    <a href="{{ route('admin.orders.show', $requestRecord->convertedOrder) }}" class="mt-3 inline-flex items-center gap-2 rounded-xl border border-brand-200 px-4 py-2.5 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">
                        {{ t('admin.custom_orders.view_order', 'View Order') }}
                        <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-4.5-6L21 3m0 0l-3.75 0M21 3v3.75"/></svg>
                    </a>
                </div>
            @endif
        </div>
    </div>
@endsection
