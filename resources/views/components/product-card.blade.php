@props(['product'])

<div class="group bg-white rounded-xl border border-gray-200 overflow-hidden hover:shadow-lg transition-shadow flex flex-col">
    <a href="{{ route('shop.show', $product->slug) }}" class="block relative aspect-square overflow-hidden bg-gray-100">
        <img src="{{ asset('storage/' . $product->image) }}" alt="{{ $product->name }}" loading="lazy"
             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
        @if($product->onSale())
            <span class="absolute top-3 left-3 bg-red-600 text-white text-xs font-bold px-2 py-1 rounded-full">
                {{ t('product.sale', 'Sale') }}
            </span>
        @endif
        @if($product->type === 'custom')
            <span class="absolute top-3 right-3 bg-brand-800 text-white text-xs font-bold px-2 py-1 rounded-full">
                {{ t('product.made_to_measure', 'Made to Measure') }}
            </span>
        @elseif($product->type === 'service')
            <span class="absolute top-3 right-3 bg-gold-500 text-white text-xs font-bold px-2 py-1 rounded-full">
                {{ t('product.badge_service', 'Service') }}
            </span>
        @endif
    </a>
    <div class="p-4 flex flex-col flex-1">
        <p class="text-xs text-gray-500 mb-1">{{ $product->category->name }}</p>
        <a href="{{ route('shop.show', $product->slug) }}" class="font-semibold text-gray-900 hover:text-brand-700 leading-snug">
            {{ $product->name }}
        </a>
        <div class="mt-auto pt-3 flex items-center justify-between">
            <div>
                @if($product->onSale())
                    <span class="text-gray-400 line-through text-sm mr-1">{{ money($product->price) }}</span>
                @endif
                <span class="font-bold text-brand-800">{{ money($product->effectivePrice()) }}</span>
            </div>
            @if($product->type === 'ready' && $product->track_stock && $product->stock <= 0)
                <span class="text-xs text-red-600 font-medium">{{ t('product.out_of_stock_short', 'Out of stock') }}</span>
            @endif
        </div>
    </div>
</div>
