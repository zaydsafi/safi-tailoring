@extends('layouts.shop')

@section('title', t('seo.order_success_title', 'Order Confirmed') . ' — ' . $shopSettings['shop_name'])

@section('robots', 'noindex, nofollow')

@section('content')
    @php
        $paymentLabels = [
            'cash_on_delivery' => t('payment.method_cash_on_delivery', 'Cash on Delivery'),
            'bank_transfer' => t('payment.method_bank_transfer', 'Bank Transfer'),
            'hesabpay' => t('payment.method_hesabpay', 'HesabPay'),
        ];
    @endphp

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
        <div class="w-16 h-16 mx-auto rounded-full bg-green-100 flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/>
            </svg>
        </div>

        <h1 class="font-display text-3xl font-bold text-gray-900 mb-2">{{ t('orders.thank_you', 'Thank You for Your Order!') }}</h1>
        <p class="text-gray-600 mb-1">{{ t('orders.placed_success', 'Your order has been placed successfully.') }}</p>
        <p class="text-gray-600 mb-6">{{ t('orders.order_number_label', 'Order number:') }} <span class="font-bold text-brand-800">{{ $order->order_number }}</span></p>

        <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 text-left mb-8">
            <h2 class="font-semibold text-gray-900 mb-3">{{ t('common.order_summary', 'Order Summary') }}</h2>
            <div class="space-y-2 text-sm">
                @foreach($order->items as $item)
                    <div class="flex justify-between gap-4">
                        <span class="text-gray-600">{{ $item->product_name }} @if($item->size) ({{ $item->size }}) @endif × {{ $item->quantity }}</span>
                        <span class="font-medium shrink-0">{{ money($item->line_total) }}</span>
                    </div>
                @endforeach
            </div>
            <div class="border-t border-gray-200 mt-3 pt-3 text-sm space-y-1">
                <div class="flex justify-between text-gray-600">
                    <span>{{ t('common.subtotal', 'Subtotal') }}</span><span>{{ money($order->subtotal) }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>{{ t('common.delivery', 'Delivery') }}</span><span>{{ $order->delivery_fee > 0 ? money($order->delivery_fee) : t('common.free', 'Free') }}</span>
                </div>
                <div class="flex justify-between font-bold text-gray-900">
                    <span>{{ t('common.total', 'Total') }}</span><span>{{ money($order->total) }}</span>
                </div>
            </div>
            <p class="text-sm text-gray-600 mt-3">
                {{ t('orders.payment', 'Payment') }}: <span class="font-medium">{{ $paymentLabels[$order->payment_method] ?? payment_method_label($order->payment_method) }}</span>
                @if($order->payment_method === 'hesabpay')
                    &middot;
                    @if($order->payment_status === 'paid')
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-green-100 text-green-800">{{ t('orders.payment_paid', 'Paid') }}</span>
                    @else
                        <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800">{{ t('orders.awaiting_payment', 'Awaiting payment') }}</span>
                    @endif
                @else
                    &middot; {{ t('orders.we_will_call', 'We will call') }} {{ $order->customer_phone }} {{ t('orders.to_confirm', 'to confirm.') }}
                @endif
            </p>
        </div>

        @php
            $confirming = session('hesabpay_confirming', false);
            $canPayHesabPay = $order->payment_method === 'hesabpay'
                && $order->payment_status !== 'paid'
                && !$confirming
                && \App\Support\HesabPay::enabled();
            $waUrl = \App\Support\WhatsApp::shopLink(t('orders.whatsapp_greeting', 'Hello! I have a question about order') . ' ' . $order->order_number . '.');
        @endphp

        @if($confirming)
            <div class="mb-8 rounded-xl border border-yellow-200 bg-yellow-50 px-5 py-4 text-sm text-yellow-900 text-left">
                {{ t('orders.hesabpay_confirming', 'We received your payment and are confirming it with HesabPay. Your order status will update automatically — no need to pay again.') }}
            </div>
        @endif

        <div class="flex flex-wrap justify-center gap-4">
            @if($canPayHesabPay)
                <form method="POST" action="{{ route('hesabpay.retry', $order) }}">
                    @csrf
                    <button type="submit" class="bg-brand-800 text-white font-medium px-6 py-3 rounded-lg hover:bg-brand-700">{{ t('orders.pay_with_hesabpay', 'Pay with HesabPay') }}</button>
                </form>
            @endif
            <a href="{{ route('orders.track') }}"
               class="font-medium px-6 py-3 rounded-lg {{ $canPayHesabPay ? 'border border-gray-300 text-gray-700 hover:bg-gray-50' : 'bg-brand-800 text-white hover:bg-brand-700' }}">{{ t('orders.track_this_order', 'Track This Order') }}</a>
            <a href="{{ route('shop.index') }}" class="border border-gray-300 text-gray-700 font-medium px-6 py-3 rounded-lg hover:bg-gray-50">{{ t('common.continue_shopping', 'Continue Shopping') }}</a>
            @if($waUrl)
                <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="bg-green-600 text-white font-medium px-6 py-3 rounded-lg hover:bg-green-700">{{ t('common.whatsapp_us', 'WhatsApp Us') }}</a>
            @endif
        </div>
    </div>
@endsection
