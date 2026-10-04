@extends('layouts.shop')

@section('title', $product->name . ' — ' . $shopSettings['shop_name'])

@section('meta_description', t('seo.product_meta_description', 'View fabric, sizing and details for this hand-stitched piece and order it made to your measurements.'))

@section('og_image', 'storage/' . $product->image)
@section('og_type', 'product')

@php
    $productSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'Product',
        'name' => $product->name,
        'image' => url('storage/' . $product->image),
        'description' => $product->short_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $product->description), 300),
        'sku' => 'ST-' . str_pad((string) $product->id, 5, '0', STR_PAD_LEFT),
        'brand' => [
            '@type' => 'Brand',
            'name' => $shopSettings['shop_name'],
        ],
        'offers' => [
            '@type' => 'Offer',
            'price' => number_format($product->effectivePrice(), 2, '.', ''),
            'priceCurrency' => $shopSettings['currency_symbol'] ?: 'AFN',
            'availability' => 'https://schema.org/InStock',
            'url' => route('shop.show', $product->slug),
        ],
    ];

    $breadcrumbSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'BreadcrumbList',
        'itemListElement' => [
            ['@type' => 'ListItem', 'position' => 1, 'name' => t('nav.home', 'Home'), 'item' => route('home')],
            ['@type' => 'ListItem', 'position' => 2, 'name' => t('nav.shop', 'Shop'), 'item' => route('shop.index')],
            ['@type' => 'ListItem', 'position' => 3, 'name' => $product->name, 'item' => route('shop.show', $product->slug)],
        ],
    ];
@endphp

@push('seo')
    <script type="application/ld+json">{!! json_encode($productSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode($breadcrumbSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
@endpush

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <nav class="text-sm text-gray-500 mb-6">
            <a href="{{ route('home') }}" class="hover:text-brand-700">{{ t('nav.home', 'Home') }}</a>
            <span class="mx-2">/</span>
            <a href="{{ route('shop.index') }}" class="hover:text-brand-700">{{ t('nav.shop', 'Shop') }}</a>
            <span class="mx-2">/</span>
            <a href="{{ route('shop.index', ['category' => $product->category->slug]) }}" class="hover:text-brand-700">{{ $product->category->name }}</a>
            <span class="mx-2">/</span>
            <span class="text-gray-900">{{ $product->name }}</span>
        </nav>

        <div class="grid md:grid-cols-2 gap-10" x-data="{ selectedImage: '{{ asset('storage/' . $product->image) }}' }">
            {{-- Gallery --}}
            <div>
                <div class="rounded-2xl overflow-hidden border border-gray-200 bg-gray-100 aspect-square">
                    <img :src="selectedImage" alt="{{ $product->name }}" class="w-full h-full object-cover">
                </div>
                @if($product->images->isNotEmpty())
                    <div class="flex gap-3 mt-3">
                        <button type="button" @click="selectedImage = '{{ asset('storage/' . $product->image) }}'"
                                class="w-20 h-20 rounded-lg overflow-hidden border-2 border-brand-600">
                            <img src="{{ asset('storage/' . $product->image) }}" alt="" class="w-full h-full object-cover">
                        </button>
                        @foreach($product->images as $image)
                            <button type="button" @click="selectedImage = '{{ asset('storage/' . $image->image) }}'"
                                    class="w-20 h-20 rounded-lg overflow-hidden border-2 border-gray-200 hover:border-brand-600">
                                <img src="{{ asset('storage/' . $image->image) }}" alt="" class="w-full h-full object-cover">
                            </button>
                        @endforeach
                    </div>
                @endif
            </div>

            {{-- Info + buy --}}
            <div>
                @if($product->type === 'custom')
                    <span class="inline-block bg-brand-100 text-brand-800 text-xs font-bold px-3 py-1 rounded-full mb-3">{{ t('product.made_to_measure', 'Made to Measure') }}</span>
                @elseif($product->type === 'service')
                    <span class="inline-block bg-gold-100 text-gold-800 text-xs font-bold px-3 py-1 rounded-full mb-3">{{ t('product.in_shop_service', 'In-Shop Service') }}</span>
                @endif

                <h1 class="font-display text-3xl font-bold text-gray-900 mb-3">{{ $product->name }}</h1>

                <div class="flex items-baseline gap-3 mb-4">
                    <span class="text-2xl font-bold text-brand-800">{{ money($product->effectivePrice()) }}</span>
                    @if($product->onSale())
                        <span class="text-lg text-gray-400 line-through">{{ money($product->price) }}</span>
                    @endif
                </div>

                @if($product->short_description)
                    <p class="text-gray-600 mb-6">{{ $product->short_description }}</p>
                @endif

                @if($product->fabric)
                    <p class="text-sm text-gray-600 mb-6"><span class="font-semibold text-gray-900">{{ t('product.fabric', 'Fabric') }}:</span> {{ $product->fabric }}</p>
                @endif

                <form method="POST" action="{{ route('cart.add') }}" class="space-y-5">
                    @csrf
                    <input type="hidden" name="product_id" value="{{ $product->id }}">

                    @if(!empty($product->sizes))
                        <div>
                            <span class="block text-sm font-semibold text-gray-900 mb-2">{{ t('product.select_size', 'Select size') }}</span>
                            <div class="flex flex-wrap gap-2">
                                @foreach($product->sizes as $size)
                                    <label>
                                        <input type="radio" name="size" value="{{ $size }}" class="peer sr-only" @checked(old('size') === $size) required>
                                        <span class="inline-block px-4 py-2 border border-gray-300 rounded-lg text-sm font-medium cursor-pointer peer-checked:bg-brand-800 peer-checked:text-white peer-checked:border-brand-800 hover:border-brand-600">
                                            {{ $size }}
                                        </span>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endif

                    <div>
                        <label for="qty" class="block text-sm font-semibold text-gray-900 mb-2">{{ t('product.quantity', 'Quantity') }}</label>
                        <input type="number" name="qty" id="qty" value="1" min="1" max="100"
                               class="w-24 rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>

                    @if($product->type === 'custom')
                        <div class="bg-brand-50 border border-brand-200 rounded-lg p-4 text-sm text-brand-900">
                            <p class="font-semibold mb-1">{{ t('product.custom_note_title', 'This item is made to your measurements.') }}</p>
                            <p>{{ t('product.custom_note_body', 'After checkout, you will be asked for your measurements (chest, waist, shoulders, and more). Our tailors begin stitching once your order is confirmed.') }}</p>
                        </div>
                    @elseif($product->type === 'service')
                        <div class="bg-gold-50 border border-gold-200 rounded-lg p-4 text-sm text-gold-900">
                            <p class="font-semibold mb-1">{{ t('product.service_note_title', 'In-shop service.') }}</p>
                            <p>{{ t('product.service_note_body', 'Bring your garment to our shop after placing the order. We will confirm the pickup time by phone.') }}</p>
                        </div>
                    @elseif($product->track_stock && $product->stock <= 0)
                        <div class="bg-red-50 border border-red-200 rounded-lg p-4 text-sm text-red-800">
                            {{ t('product.out_of_stock', 'This item is currently out of stock.') }}
                        </div>
                    @endif

                    @if(!($product->type === 'ready' && $product->track_stock && $product->stock <= 0))
                        <button type="submit" class="w-full sm:w-auto bg-brand-800 text-white font-semibold px-8 py-3 rounded-lg hover:bg-brand-700">
                            {{ t('product.add_to_cart', 'Add to Cart') }}
                        </button>
                    @endif
                </form>

                @php $waProduct = \App\Support\WhatsApp::shopLink(t('product.whatsapp_message', 'Hello! I am interested in') . ' "' . $product->name . '" (' . money($product->effectivePrice()) . '). ' . t('product.whatsapp_question', 'Could you tell me more about it?')); @endphp
                @if($waProduct)
                    <a href="{{ $waProduct }}" target="_blank" rel="noopener"
                       class="mt-4 inline-flex items-center gap-2 text-green-700 font-medium text-sm hover:underline">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        {{ t('product.whatsapp_ask', 'Ask about this item on WhatsApp') }}
                    </a>
                @endif

                <div class="mt-8 border-t border-gray-200 pt-6">
                    <h2 class="font-semibold text-gray-900 mb-2">{{ t('product.description_heading', 'Description') }}</h2>
                    <p class="text-gray-600 text-sm leading-relaxed whitespace-pre-line">{{ $product->description }}</p>
                </div>
            </div>
        </div>

        @if($related->isNotEmpty())
            <section class="mt-16">
                <h2 class="font-display text-2xl font-bold text-brand-900 mb-6">{{ t('product.you_may_also_like', 'You may also like') }}</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                    @foreach($related as $item)
                        <x-product-card :product="$item" />
                    @endforeach
                </div>
            </section>
        @endif
    </div>
@endsection
