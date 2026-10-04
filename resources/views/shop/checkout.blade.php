@extends('layouts.shop')

@section('title', t('seo.checkout_title', 'Checkout') . ' — ' . $shopSettings['shop_name'])

@section('robots', 'noindex, nofollow')

@section('content')
    @php
        $customItems = $cartItems->where('product.type', 'custom');
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
        $profilesJson = $savedProfiles->map(fn ($p) => [
            'id' => $p->id,
            'label' => $p->label,
            'data' => collect(\App\Models\MeasurementProfile::fields())->keys()->mapWithKeys(fn ($f) => [$f => $p->{$f}])->all(),
        ])->values()->all();
    @endphp

    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-10"
         x-data="{ profileData: {{ \Illuminate\Support\Js::from($profilesJson) }}, selectedProfile: '' }">
        <h1 class="font-display text-3xl font-bold text-brand-900 mb-8">{{ t('checkout.title', 'Checkout') }}</h1>

        <form method="POST" action="{{ route('checkout.store') }}" class="grid lg:grid-cols-3 gap-10">
            @csrf

            <div class="lg:col-span-2 space-y-8">
                {{-- Contact & delivery --}}
                <section class="bg-white border border-gray-200 rounded-xl p-6">
                    <h2 class="font-semibold text-lg text-gray-900 mb-4">{{ t('checkout.contact_delivery', 'Contact & Delivery Details') }}</h2>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label for="customer_name" class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.full_name', 'Full name') }} *</label>
                            <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name', auth()->user()->name ?? '') }}" required
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <div>
                            <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.phone', 'Phone') }} *</label>
                            <input type="text" name="customer_phone" id="customer_phone" value="{{ old('customer_phone', auth()->user()->phone ?? '') }}" required
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="customer_email" class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.email_optional', 'Email (optional)') }}</label>
                            <input type="email" name="customer_email" id="customer_email" value="{{ old('customer_email', auth()->user()->email ?? '') }}"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <div class="sm:col-span-2">
                            <label for="customer_address" class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.delivery_address', 'Delivery address') }} *</label>
                            <input type="text" name="customer_address" id="customer_address" value="{{ old('customer_address', auth()->user()->address ?? '') }}" required
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <div>
                            <label for="customer_city" class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.city', 'City') }}</label>
                            <input type="text" name="customer_city" id="customer_city" value="{{ old('customer_city', auth()->user()->city ?? '') }}"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                        </div>
                        <div>
                            <label for="notes" class="block text-sm font-medium text-gray-700 mb-1">{{ t('checkout.order_notes', 'Order notes (optional)') }}</label>
                            <input type="text" name="notes" id="notes" value="{{ old('notes') }}" placeholder="{{ t('checkout.notes_placeholder', 'Any special instructions') }}"
                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                        </div>
                    </div>
                </section>

                {{-- Measurements for custom items --}}
                @if($customItems->isNotEmpty())
                    <section class="bg-white border border-gray-200 rounded-xl p-6">
                        <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
                            <h2 class="font-semibold text-lg text-gray-900">{{ t('checkout.measurements_heading', 'Your Measurements (inches)') }}</h2>
                            @auth
                                @if($savedProfiles->isNotEmpty())
                                    <div class="flex items-center gap-2 text-sm">
                                        <label class="text-gray-600">{{ t('checkout.apply_saved_profile', 'Apply saved profile:') }}</label>
                                        <select x-model="selectedProfile" @change="
                                            const p = profileData.find(x => String(x.id) === selectedProfile);
                                            if (p) {
                                                document.querySelectorAll('[data-measure-field]').forEach(input => {
                                                    const v = p.data[input.dataset.measureField];
                                                    if (v !== null && v !== undefined) input.value = v;
                                                });
                                            }
                                        " class="rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                                            <option value="">{{ t('checkout.choose', 'Choose...') }}</option>
                                            @foreach($savedProfiles as $profile)
                                                <option value="{{ $profile->id }}">{{ $profile->label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                @endif
                            @endauth
                        </div>
                        <p class="text-sm text-gray-500 mb-5">{{ t('checkout.measurements_hint', 'Enter at least one measurement for each made-to-measure item. Unsure? Our tailor will call you after ordering to confirm.') }}</p>

                        @foreach($customItems as $item)
                            <div class="border border-gray-200 rounded-lg p-4 mb-4 last:mb-0">
                                <p class="font-medium text-gray-900 mb-3">{{ $item->product->name }} <span class="text-gray-400 font-normal">× {{ $item->qty }}</span></p>
                                <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                                    @foreach($measurementFields as $field => $label)
                                        <div>
                                            <label class="block text-xs font-medium text-gray-600 mb-1">{{ $measureLabels[$field] ?? $label }}</label>
                                            <input type="number" step="0.5" min="1" max="300"
                                                   name="measurements[{{ $item->key }}][{{ $field }}]"
                                                   value="{{ old('measurements.' . $item->key . '.' . $field) }}"
                                                   data-measure-field="{{ $field }}"
                                                   class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach

                        @auth
                            <div class="mt-4 flex flex-wrap items-center gap-3 bg-gray-50 rounded-lg p-3">
                                <label class="flex items-center gap-2 text-sm text-gray-700">
                                    <input type="checkbox" name="save_measurements" value="1" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500" @checked(old('save_measurements'))>
                                    {{ t('checkout.save_measurements', 'Save these measurements to my account') }}
                                </label>
                                <input type="text" name="profile_label" value="{{ old('profile_label') }}" placeholder="{{ t('checkout.profile_label_placeholder', 'Profile label (e.g. My usual size)') }}"
                                       class="rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                            </div>
                        @endauth
                    </section>
                @endif

                {{-- Payment method --}}
                <section class="bg-white border border-gray-200 rounded-xl p-6">
                    <h2 class="font-semibold text-lg text-gray-900 mb-4">{{ t('checkout.payment_method', 'Payment Method') }}</h2>
                    <div class="space-y-3">
                        <label class="flex items-start gap-3 border border-gray-200 rounded-lg p-4 cursor-pointer has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                            <input type="radio" name="payment_method" value="cash_on_delivery" class="mt-1 text-brand-600 focus:ring-brand-500" @checked(old('payment_method', 'cash_on_delivery') === 'cash_on_delivery')>
                            <span>
                                <span class="block font-medium text-gray-900">{{ t('payment.method_cash_on_delivery', 'Cash on Delivery') }}</span>
                                <span class="block text-sm text-gray-500">{{ t('checkout.cash_on_delivery_desc', 'Pay when your order arrives at your door.') }}</span>
                            </span>
                        </label>
                        <label class="flex items-start gap-3 border border-gray-200 rounded-lg p-4 cursor-pointer has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                            <input type="radio" name="payment_method" value="bank_transfer" class="mt-1 text-brand-600 focus:ring-brand-500" @checked(old('payment_method') === 'bank_transfer')>
                            <span>
                                <span class="block font-medium text-gray-900">{{ t('payment.method_bank_transfer', 'Bank Transfer') }}</span>
                                <span class="block text-sm text-gray-500">{{ t('checkout.bank_transfer_desc', 'We will contact you with our bank details after you place the order.') }}</span>
                            </span>
                        </label>
                        @if($hesabpayEnabled)
                            <label class="flex items-start gap-3 border border-gray-200 rounded-lg p-4 cursor-pointer has-[:checked]:border-brand-600 has-[:checked]:bg-brand-50">
                                <input type="radio" name="payment_method" value="hesabpay" class="mt-1 text-brand-600 focus:ring-brand-500" @checked(old('payment_method') === 'hesabpay')>
                                <span>
                                    <span class="block font-medium text-gray-900">{{ t('payment.method_hesabpay', 'HesabPay') }}</span>
                                    <span class="block text-sm text-gray-500">{{ t('checkout.hesabpay_desc', "Pay now with HesabPay. After placing the order you will be redirected to HesabPay's secure checkout to complete the payment.") }}</span>
                                </span>
                            </label>
                        @endif
                    </div>
                </section>
            </div>

            {{-- Order summary --}}
            <aside class="lg:sticky lg:top-24 h-fit bg-gray-50 border border-gray-200 rounded-xl p-6">
                <h2 class="font-semibold text-lg text-gray-900 mb-4">{{ t('common.order_summary', 'Order Summary') }}</h2>
                <div class="space-y-3 text-sm max-h-64 overflow-y-auto pr-1">
                    @foreach($cartItems as $item)
                        <div class="flex justify-between gap-3">
                            <span class="text-gray-600">{{ $item->product->name }} @if($item->size) ({{ $item->size }}) @endif × {{ $item->qty }}</span>
                            <span class="font-medium shrink-0">{{ money($item->line_total) }}</span>
                        </div>
                    @endforeach
                </div>
                <div class="border-t border-gray-200 mt-4 pt-4 space-y-2 text-sm">
                    <div class="flex justify-between text-gray-700">
                        <span>{{ t('common.subtotal', 'Subtotal') }}</span>
                        <span>{{ money($subtotal) }}</span>
                    </div>
                    <div class="flex justify-between text-gray-700">
                        <span>{{ t('common.delivery', 'Delivery') }}</span>
                        <span>{{ $deliveryFee > 0 ? money($deliveryFee) : t('common.free', 'Free') }}</span>
                    </div>
                    <div class="flex justify-between font-bold text-base text-gray-900 border-t border-gray-200 pt-2">
                        <span>{{ t('common.total', 'Total') }}</span>
                        <span class="text-brand-800">{{ money($subtotal + $deliveryFee) }}</span>
                    </div>
                </div>
                <button type="submit" class="w-full mt-6 bg-brand-800 text-white font-semibold py-3 rounded-lg hover:bg-brand-700">
                    {{ t('checkout.place_order', 'Place Order') }}
                </button>
                <p class="text-xs text-gray-500 mt-3 text-center">{{ t('checkout.terms', "By placing this order you agree to our shop's terms of service.") }}</p>
            </aside>
        </form>
    </div>
@endsection
