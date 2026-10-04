@extends('layouts.shop')

@section('title', t('seo.shop_title', 'Shop') . ' — ' . $shopSettings['shop_name'])

@section('meta_description', t('seo.shop_meta_description', 'Browse our full collection of ready-to-wear and made-to-measure garments, hand-stitched by master tailors.'))

@if(request()->filled('q'))
    @section('robots', 'noindex, follow')
@endif

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="flex flex-col lg:flex-row gap-8">
            {{-- Sidebar filters --}}
            <aside class="lg:w-64 shrink-0">
                <form method="GET" action="{{ route('shop.index') }}" class="space-y-6 lg:sticky lg:top-24">
                    <div>
                        <label for="q" class="block text-sm font-semibold text-gray-900 mb-2">{{ t('shop.search_label', 'Search') }}</label>
                        <input type="text" name="q" id="q" value="{{ request('q') }}" placeholder="{{ t('shop.search_placeholder', 'Search products...') }}"
                               class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                    </div>

                    <div>
                        <span class="block text-sm font-semibold text-gray-900 mb-2">{{ t('shop.category_label', 'Category') }}</span>
                        <div class="space-y-1">
                            <a href="{{ route('shop.index', array_merge(request()->except('category', 'page'), ['category' => null])) }}"
                               class="block text-sm px-3 py-2 rounded-lg {{ request('category') ? 'text-gray-600 hover:bg-gray-100' : 'bg-brand-100 text-brand-900 font-medium' }}">
                                {{ t('shop.all_categories', 'All Categories') }}
                            </a>
                            @foreach($categories as $category)
                                <a href="{{ route('shop.index', array_merge(request()->except('page'), ['category' => $category->slug])) }}"
                                   class="block text-sm px-3 py-2 rounded-lg {{ request('category') === $category->slug ? 'bg-brand-100 text-brand-900 font-medium' : 'text-gray-600 hover:bg-gray-100' }}">
                                    {{ $category->name }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div>
                        <label for="type" class="block text-sm font-semibold text-gray-900 mb-2">{{ t('shop.type_label', 'Type') }}</label>
                        <select name="type" id="type" class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="">{{ t('shop.all_types', 'All types') }}</option>
                            <option value="ready" @selected(request('type') === 'ready')>{{ t('shop.type_ready', 'Ready to wear') }}</option>
                            <option value="custom" @selected(request('type') === 'custom')>{{ t('shop.type_custom', 'Made to measure') }}</option>
                            <option value="service" @selected(request('type') === 'service')>{{ t('shop.type_service', 'Services') }}</option>
                        </select>
                    </div>

                    <div>
                        <label for="sort" class="block text-sm font-semibold text-gray-900 mb-2">{{ t('shop.sort_label', 'Sort by') }}</label>
                        <select name="sort" id="sort" class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                            <option value="latest" @selected(request('sort', 'latest') === 'latest')>{{ t('shop.sort_newest', 'Newest') }}</option>
                            <option value="oldest" @selected(request('sort') === 'oldest')>{{ t('shop.sort_oldest', 'Oldest') }}</option>
                            <option value="price_asc" @selected(request('sort') === 'price_asc')>{{ t('shop.sort_price_asc', 'Price: low to high') }}</option>
                            <option value="price_desc" @selected(request('sort') === 'price_desc')>{{ t('shop.sort_price_desc', 'Price: high to low') }}</option>
                        </select>
                    </div>

                    <button type="submit" class="w-full bg-brand-800 text-white font-medium py-2.5 rounded-lg hover:bg-brand-700 text-sm">
                        {{ t('shop.apply_filters', 'Apply Filters') }}
                    </button>
                </form>
            </aside>

            {{-- Product grid --}}
            <div class="flex-1">
                <div class="flex items-center justify-between mb-6">
                    <h1 class="font-display text-2xl font-bold text-brand-900">
                        {{ request('category') ? $categories->firstWhere('slug', request('category'))?->name ?? t('shop.heading', 'Shop') : t('shop.heading', 'Shop') }}
                    </h1>
                    <p class="text-sm text-gray-500">{{ $products->total() }} {{ $products->total() === 1 ? t('shop.product_count', 'product') : t('shop.product_count_plural', 'products') }}</p>
                </div>

                @if($products->isEmpty())
                    <div class="text-center py-20 bg-gray-50 rounded-xl border border-dashed border-gray-300">
                        <p class="text-gray-500 mb-4">{{ t('shop.no_products', 'No products match your filters.') }}</p>
                        <a href="{{ route('shop.index') }}" class="text-brand-700 font-medium hover:underline">{{ t('shop.clear_filters', 'Clear filters') }}</a>
                    </div>
                @else
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 md:gap-6">
                        @foreach($products as $product)
                            <x-product-card :product="$product" />
                        @endforeach
                    </div>

                    <div class="mt-10">
                        {{ $products->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endsection
