@extends('layouts.admin')

@section('title', t('admin.custom_orders.create_for_customer_title', 'Create Order for Customer'))

@section('content')
    @php
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

        $savedProfile = null;

        if ($selectedCustomer && (request()->boolean('use_existing_measurements') || old('use_existing_measurements'))) {
            $savedProfile = $selectedCustomer->measurementProfiles()->latest()->first();
        }
    @endphp

    <div class="mb-6">
        <a href="{{ route('admin.custom-orders.index') }}" class="text-sm font-semibold text-brand-500 hover:text-brand-700">{{ t('admin.custom_orders.back_to_requests', '← Back to custom orders') }}</a>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="xl:col-span-2 space-y-6">
            <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-bold text-brand-900">{{ t('admin.custom_orders.select_customer', 'Select Customer') }}</h2>
                <form method="GET" class="flex flex-wrap items-center gap-3">
                    <select name="customer_id" id="customer_id"
                            class="rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('customer_id') border-red-400 @enderror">
                        <option value="">{{ t('admin.custom_orders.pick_customer', 'Pick a customer…') }}</option>
                        @foreach ($customers as $customer)
                            <option value="{{ $customer->id }}" @selected(old('customer_id', $selectedCustomer?->id) === $customer->id)>
                                {{ $customer->name }} — {{ $customer->phone }}
                                @if ($customer->measurementProfiles()->exists()) · {{ t('admin.custom_orders.has_profile', 'has measurements') }} @endif
                            </option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">{{ t('admin.common.filter', 'Filter') }}</button>
                </form>
            </div>

            @if ($selectedCustomer)
                <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-base font-bold text-brand-900">{{ $selectedCustomer->name }}</h2>
                            <p class="text-sm text-brand-600">{{ $selectedCustomer->email }}</p>
                            <p class="text-sm text-brand-600">{{ $selectedCustomer->phone }}</p>
                            @if ($selectedCustomer->address)
                                <p class="text-sm text-brand-600">{{ $selectedCustomer->address }}@if($selectedCustomer->city) · {{ $selectedCustomer->city }}@endif</p>
                            @endif
                        </div>
                        <a href="{{ route('admin.customers.show', $selectedCustomer) }}" class="rounded-lg border border-brand-200 px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:bg-brand-50">
                            {{ t('admin.common.view', 'View Customer') }}
                        </a>
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('admin.custom-orders.store') }}" class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                @csrf
                <h2 class="mb-4 text-base font-bold text-brand-900">{{ t('admin.custom_orders.new_order', 'New Order') }}</h2>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label for="customer_name" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.common.name', 'Name') }} <span class="text-red-500">*</span></label>
                        <input type="text" id="customer_name" name="customer_name" value="{{ old('customer_name', $selectedCustomer?->name ?? '') }}" required
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('customer_name') border-red-400 @enderror">
                        @error('customer_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="customer_phone" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.common.phone', 'Phone') }} <span class="text-red-500">*</span></label>
                        <input type="text" id="customer_phone" name="customer_phone" value="{{ old('customer_phone', $selectedCustomer?->phone ?? '') }}" required
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('customer_phone') border-red-400 @enderror">
                        @error('customer_phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="customer_email" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.common.email', 'Email') }}</label>
                        <input type="email" id="customer_email" name="customer_email" value="{{ old('customer_email', $selectedCustomer?->email ?? '') }}"
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('customer_email') border-red-400 @enderror">
                        @error('customer_email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="customer_address" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.common.address', 'Address') }} <span class="text-red-500">*</span></label>
                        <input type="text" id="customer_address" name="customer_address" value="{{ old('customer_address', $selectedCustomer?->address ?? '') }}" required
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('customer_address') border-red-400 @enderror">
                        @error('customer_address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="customer_city" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.custom_orders.city', 'City') }}</label>
                        <input type="text" id="customer_city" name="customer_city" value="{{ old('customer_city', $selectedCustomer?->city ?? '') }}"
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('customer_city') border-red-400 @enderror">
                        @error('customer_city')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="notes" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.custom_orders.order_notes', 'Order Notes') }}</label>
                        <input type="text" id="notes" name="notes" value="{{ old('notes') }}" placeholder="{{ t('checkout.notes_placeholder', 'Any special instructions') }}"
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('notes') border-red-400 @enderror">
                        @error('notes')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-6">
                    <div class="flex items-center gap-3 mb-3">
                        @if (request()->boolean('use_existing_measurements') || $selectedCustomer?->measurementProfiles()->exists())
                        <label class="flex items-center gap-2 text-sm text-brand-700">
                            <input type="checkbox" name="use_existing_measurements" value="1" id="use_existing_measurements" @checked(request()->boolean('use_existing_measurements'))>
                            {{ t('admin.custom_orders.use_existing_measurements', 'Use saved measurements from this customer') }}
                        </label>
                    @endif
                    </div>
                    @if ($savedProfile)
                        <div class="rounded-lg bg-brand-50 px-4 py-3 text-sm text-brand-700 mb-4">
                            <span class="font-semibold">{{ t('admin.custom_orders.saved_profile', 'Saved profile') }}:</span> {{ $savedProfile->label }} —
                            @foreach ($measurementFields as $field => $label)
                                @if ($savedProfile->$field !== null)
                                    {{ $label }}: {{ $savedProfile->$field }}″
                                    @if (!$loop->last) · @endif
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>

                <div class="mt-4 space-y-4">
                    <div class="flex items-center justify-between">
                        <label class="font-semibold text-brand-900">{{ t('admin.custom_orders.add_item', 'Add Custom Item') }}</label>
                        <button type="button" class="rounded-lg border border-brand-200 px-3 py-1.5 text-xs font-semibold text-brand-700 hover:bg-brand-50" id="add-item-btn">
                            {{ t('admin.custom_orders.add_another', '+ Add Another') }}
                        </button>
                    </div>
                    <div id="items-container" class="space-y-4">
                        @foreach (old('items', [[
                            'description' => old('items.0.description'),
                            'measurements' => old('items.0.measurements'),
                            'price' => old('items.0.price'),
                            'quantity' => old('items.0.quantity'),
                        ]]) as $index => $item)
                            @include('admin.custom-orders._item-row', ['index' => $index, 'item' => $item, 'measurementFields' => $measurementFields, 'savedProfile' => $savedProfile])
                        @endforeach
                    </div>
                </div>

                @if (old('custom_order_request_id', request()->input('custom_order_request_id')))
                    <input type="hidden" name="custom_order_request_id" value="{{ old('custom_order_request_id') }}">
                @endif

                <div class="mt-6 flex flex-wrap gap-3">
                    <button type="submit" class="rounded-xl bg-teal-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-teal-800">
                        {{ t('admin.custom_orders.create_order', 'Create Order') }}
                    </button>
                    <a href="{{ route('admin.custom-orders.index') }}" class="rounded-xl border border-brand-200 px-6 py-3 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">
                        {{ t('admin.common.cancel', 'Cancel') }}
                    </a>
                </div>
            </form>
        </div>

        <div>
            <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-bold text-brand-900">{{ t('admin.custom_orders.summary', 'Summary') }}</h2>
                <div class="text-sm space-y-2 text-brand-700">
                    <div class="flex justify-between"><span class="font-medium text-brand-900">{{ t('admin.common.items', 'Items') }}</span><span id="item-count">0</span></div>
                    <div class="flex justify-between"><span class="font-medium text-brand-900">{{ t('common.subtotal', 'Subtotal') }}</span><span id="subtotal">{{ money(0) }}</span></div>
                    <div class="flex justify-between"><span class="font-medium text-brand-900">{{ t('common.delivery', 'Delivery') }}</span><span id="delivery-fee">{{ money(0) }}</span></div>
                    <div class="flex justify-between border-t border-brand-100 pt-2 text-base"><span class="font-bold text-brand-900">{{ t('common.total', 'Total') }}</span><span id="total" class="font-bold text-brand-800">{{ money(0) }}</span></div>
                </div>
                <p class="mt-4 text-xs text-brand-400">{{ t('admin.custom_orders.summary_note', 'Delivery fee follows the shop settings. The order will be created with cash on delivery until payment is collected separately.') }}</p>
            </div>
        </div>
    </div>

    <script type="application/json" id="measurement-fields">{{ json_encode($measurementFields) }}</script>
@endsection
