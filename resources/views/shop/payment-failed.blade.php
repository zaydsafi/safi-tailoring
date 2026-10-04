@extends('layouts.shop')

@section('title', t('seo.payment_failed_title', 'Payment Not Completed') . ' — ' . $shopSettings['shop_name'])

@section('robots', 'noindex, nofollow')

@section('content')
    @php
        $paymentLabels = [
            'cash_on_delivery' => t('payment.method_cash_on_delivery', 'Cash on Delivery'),
            'bank_transfer' => t('payment.method_bank_transfer', 'Bank Transfer'),
            'hesabpay' => t('payment.method_hesabpay', 'HesabPay'),
        ];
        $canRetry = $order->payment_method === 'hesabpay'
            && $order->payment_status !== 'paid'
            && \App\Support\HesabPay::enabled();
        $waHelp = \App\Support\WhatsApp::shopLink(t('orders.whatsapp_payment_help', 'Hello! I need help with the payment for order') . ' ' . $order->order_number . '.');
    @endphp

    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-16 text-center">
        <div class="w-16 h-16 mx-auto rounded-full bg-amber-100 flex items-center justify-center mb-6">
            <svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v3.75m0 3.75h.008v.008H12v-.008ZM21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z"/>
            </svg>
        </div>

        <h1 class="font-display text-3xl font-bold text-gray-900 mb-2">{{ t('orders.payment_not_completed', 'Payment Not Completed') }}</h1>
        <p class="text-gray-600 mb-2">{{ $message }}</p>
        <p class="text-gray-600 mb-8">
            {{ t('orders.order_label', 'Order') }} <span class="font-bold text-brand-800">{{ $order->order_number }}</span>
            {{ t('orders.is_saved', 'is saved') }}{{ $order->payment_status !== 'paid' ? ' ' . t('orders.as_unpaid', 'as unpaid') : '' }} — {{ t('orders.total_lower', 'total') }} {{ money($order->total) }}.
        </p>

        <div class="bg-gray-50 border border-gray-200 rounded-xl p-6 text-left mb-8">
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
                    <span>{{ t('common.delivery', 'Delivery') }}</span><span>{{ $order->delivery_fee > 0 ? money($order->delivery_fee) : t('common.free', 'Free') }}</span>
                </div>
                <div class="flex justify-between font-bold text-gray-900">
                    <span>{{ t('common.total', 'Total') }}</span><span>{{ money($order->total) }}</span>
                </div>
                <div class="flex justify-between text-gray-600">
                    <span>{{ t('orders.payment', 'Payment') }}</span><span>{{ $paymentLabels[$order->payment_method] ?? payment_method_label($order->payment_method) }}</span>
                </div>
            </div>
        </div>

        <div class="flex flex-wrap justify-center gap-4">
            @if($canRetry)
                <form method="POST" action="{{ route('hesabpay.retry', $order) }}">
                    @csrf
                    <button type="submit" class="bg-brand-800 text-white font-medium px-6 py-3 rounded-lg hover:bg-brand-700">
                        {{ t('orders.pay_with_hesabpay', 'Pay with HesabPay') }}
                    </button>
                </form>
            @endif
            <a href="{{ route('orders.track') }}" class="border border-gray-300 text-gray-700 font-medium px-6 py-3 rounded-lg hover:bg-gray-50">{{ t('orders.track_order', 'Track Order') }}</a>
            @if($waHelp)
                <a href="{{ $waHelp }}" target="_blank" rel="noopener"
                   class="bg-green-600 text-white font-medium px-6 py-3 rounded-lg hover:bg-green-700">{{ t('common.whatsapp_us', 'WhatsApp Us') }}</a>
            @endif
        </div>
    </div>
@endsection
