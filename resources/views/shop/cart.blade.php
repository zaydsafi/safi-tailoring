@extends('layouts.shop')

@section('title', t('seo.cart_title', 'Cart') . ' — ' . $shopSettings['shop_name'])

@section('robots', 'noindex, nofollow')

@section('content')
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <h1 class="font-display text-3xl font-bold text-brand-900 mb-8">{{ t('cart.heading', 'Your Cart') }}</h1>

        @if($cartItems->isEmpty())
            <div class="text-center py-20 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                <p class="text-gray-500 mb-4">{{ t('cart.empty', 'Your cart is empty.') }}</p>
                <a href="{{ route('shop.index') }}" class="inline-block bg-brand-800 text-white font-medium px-6 py-3 rounded-lg hover:bg-brand-700">
                    {{ t('common.continue_shopping', 'Continue Shopping') }}
                </a>
            </div>
        @else
            <div class="space-y-4">
                @foreach($cartItems as $item)
                    <div class="flex gap-4 bg-white border border-gray-200 rounded-xl p-4">
                        <a href="{{ route('shop.show', $item->product->slug) }}" class="shrink-0">
                            <img src="{{ asset('storage/' . $item->product->image) }}" alt="{{ $item->product->name }}"
                                 class="w-24 h-24 rounded-lg object-cover">
                        </a>
                        <div class="flex-1 min-w-0">
                            <a href="{{ route('shop.show', $item->product->slug) }}" class="font-semibold text-gray-900 hover:text-brand-700">
                                {{ $item->product->name }}
                            </a>
                            <p class="text-sm text-gray-500 mt-1">
                                @if($item->size) {{ t('common.size', 'Size') }}: {{ $item->size }} @endif
                                @if($item->product->type === 'custom') {{ t('product.made_to_measure', 'Made to measure') }} @endif
                                @if($item->product->type === 'service') {{ t('product.in_shop_service', 'In-shop service') }} @endif
                            </p>
                            <p class="text-sm font-medium text-gray-700 mt-1">{{ money($item->price) }} {{ t('cart.each', 'each') }}</p>

                            <div class="flex flex-wrap items-center gap-4 mt-3">
                                <form method="POST" action="{{ route('cart.update', $item->key) }}" class="flex items-center gap-2">
                                    @csrf
                                    @method('PATCH')
                                    <label class="text-xs text-gray-500">{{ t('cart.qty', 'Qty') }}</label>
                                    <input type="number" name="qty" value="{{ $item->qty }}" min="1" max="100"
                                           class="w-16 rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500"
                                           onchange="this.form.submit()">
                                </form>

                                <form method="POST" action="{{ route('cart.remove', $item->key) }}"
                                      onsubmit="return confirm('{{ t('cart.confirm_remove', 'Remove this item from your cart?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm text-red-600 hover:underline">{{ t('cart.remove', 'Remove') }}</button>
                                </form>
                            </div>
                        </div>
                        <div class="text-right shrink-0">
                            <p class="font-bold text-brand-800">{{ money($item->line_total) }}</p>
                        </div>
                    </div>
                @endforeach
            </div>

            <div class="mt-8 bg-gray-50 rounded-xl p-6 border border-gray-200 max-w-md ml-auto">
                <div class="flex justify-between text-gray-700 mb-2">
                    <span>{{ t('common.subtotal', 'Subtotal') }}</span>
                    <span class="font-semibold">{{ money($subtotal) }}</span>
                </div>
                <p class="text-xs text-gray-500 mb-4">{{ t('cart.delivery_note', 'Delivery fee is calculated at checkout.') }}</p>
                <a href="{{ route('checkout') }}" class="block w-full text-center bg-brand-800 text-white font-semibold py-3 rounded-lg hover:bg-brand-700">
                    {{ t('cart.proceed_to_checkout', 'Proceed to Checkout') }}
                </a>
                <a href="{{ route('shop.index') }}" class="block w-full text-center mt-3 text-sm text-brand-700 hover:underline">
                    {{ t('common.continue_shopping', 'Continue Shopping') }}
                </a>
            </div>
        @endif
    </div>
@endsection
