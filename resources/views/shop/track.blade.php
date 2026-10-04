@extends('layouts.shop')

@section('title', t('seo.track_title', 'Track Order') . ' — ' . $shopSettings['shop_name'])

@section('meta_description', t('seo.track_meta_description', 'Enter your order number and phone number to see the latest status of your tailoring order.'))

@section('content')
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="font-display text-3xl font-bold text-brand-900 mb-2 text-center">{{ t('track.heading', 'Track Your Order') }}</h1>
        <p class="text-gray-600 text-center mb-8">{{ t('track.intro', 'Enter your order number and the phone number used at checkout.') }}</p>

        <form method="POST" action="{{ route('orders.track.lookup') }}" class="bg-white border border-gray-200 rounded-xl p-6 mb-8">
            @csrf
            <div class="grid sm:grid-cols-2 gap-4 mb-4">
                <div>
                    <label for="order_number" class="block text-sm font-medium text-gray-700 mb-1">{{ t('track.order_number', 'Order number') }} *</label>
                    <input type="text" name="order_number" id="order_number" value="{{ old('order_number') }}" placeholder="{{ t('track.order_number_placeholder', 'e.g. ST-2610-00042') }}" required
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label for="customer_phone" class="block text-sm font-medium text-gray-700 mb-1">{{ t('track.phone_number', 'Phone number') }} *</label>
                    <input type="text" name="customer_phone" id="customer_phone" value="{{ old('customer_phone') }}" required
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
            </div>
            <button type="submit" class="w-full bg-brand-800 text-white font-medium py-2.5 rounded-lg hover:bg-brand-700">{{ t('track.submit', 'Track Order') }}</button>
        </form>

        @if(isset($order))
            <div class="bg-white border border-gray-200 rounded-xl p-6">
                <div class="flex flex-wrap items-center justify-between gap-3 mb-6">
                    <div>
                        <p class="text-sm text-gray-500">{{ t('track.order_label', 'Order') }}</p>
                        <p class="font-bold text-lg text-gray-900">{{ $order->order_number }}</p>
                    </div>
                    <span class="px-3 py-1 rounded-full text-xs font-bold {{ status_badge_class($order->status) }}">{{ $order->statusLabel() }}</span>
                </div>

                @php
                    $steps = [
                        'pending' => t('track.status_pending', 'Pending'),
                        'confirmed' => t('track.status_confirmed', 'Confirmed'),
                        'in_tailoring' => t('track.status_in_tailoring', 'In Tailoring'),
                        'ready' => t('track.status_ready', 'Ready'),
                        'delivered' => t('track.status_delivered', 'Delivered'),
                    ];
                    $currentIndex = $order->status === 'cancelled' ? -1 : array_search($order->status, array_keys($steps));
                @endphp

                @if($order->status === 'cancelled')
                    <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg p-4 text-sm">
                        {{ t('track.cancelled_note', 'This order was cancelled. Please contact us at') }} {{ $shopSettings['shop_phone'] }} {{ t('track.cancelled_note_suffix', 'for more information.') }}
                    </div>
                @else
                    <ol class="space-y-0">
                        @foreach($steps as $key => $label)
                            @php $stepIndex = array_search($key, array_keys($steps)); @endphp
                            <li class="flex items-start gap-3">
                                <div class="flex flex-col items-center">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold
                                        {{ $stepIndex <= $currentIndex ? 'bg-brand-700 text-white' : 'bg-gray-200 text-gray-500' }}">
                                        {{ $stepIndex + 1 }}
                                    </div>
                                    @if(!$loop->last)
                                        <div class="w-0.5 h-8 {{ $stepIndex < $currentIndex ? 'bg-brand-700' : 'bg-gray-200' }}"></div>
                                    @endif
                                </div>
                                <p class="pt-1.5 text-sm font-medium {{ $stepIndex <= $currentIndex ? 'text-gray-900' : 'text-gray-400' }}">{{ $label }}</p>
                            </li>
                        @endforeach
                    </ol>
                @endif

                <div class="border-t border-gray-200 mt-6 pt-4">
                    <h3 class="font-semibold text-gray-900 text-sm mb-2">{{ t('track.items', 'Items') }}</h3>
                    <div class="space-y-1 text-sm">
                        @foreach($order->items as $item)
                            <div class="flex justify-between gap-4">
                                <span class="text-gray-600">{{ $item->product_name }} @if($item->size) ({{ $item->size }}) @endif × {{ $item->quantity }}</span>
                                <span class="font-medium">{{ money($item->line_total) }}</span>
                            </div>
                        @endforeach
                    </div>
                    <div class="flex justify-between font-bold text-gray-900 text-sm mt-3">
                        <span>{{ t('track.total', 'Total') }} ({{ $order->payment_status === 'paid' ? t('common.paid', 'paid') : t('common.pay_on_delivery', 'pay on delivery') }})</span>
                        <span>{{ money($order->total) }}</span>
                    </div>
                </div>

                @if($order->statusHistories->isNotEmpty())
                    <div class="border-t border-gray-200 mt-6 pt-4">
                        <h3 class="font-semibold text-gray-900 text-sm mb-2">{{ t('track.history', 'History') }}</h3>
                        <ul class="text-sm text-gray-600 space-y-1">
                            @foreach($order->statusHistories()->oldest()->get() as $history)
                                <li>{{ $history->created_at->format('M d, Y H:i') }} — {{ \App\Models\Order::STATUSES[$history->status] ?? $history->status }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                @php $waUrl = \App\Support\WhatsApp::shopLink(t('track.whatsapp_message', 'Hello! Please share an update on my order') . ' ' . $order->order_number . '.'); @endphp
                @if($waUrl)
                    <div class="border-t border-gray-200 mt-6 pt-4 text-center">
                        <a href="{{ $waUrl }}" target="_blank" rel="noopener" class="inline-flex items-center gap-2 text-green-700 font-medium text-sm hover:underline">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                            {{ t('track.whatsapp_ask', 'Ask for an update on WhatsApp') }}
                        </a>
                    </div>
                @endif
            </div>
        @endif
    </div>
@endsection
