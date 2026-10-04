@extends('layouts.shop')

@section('title', t('seo.home_title', 'Home') . ' — ' . $shopSettings['shop_name'])

@section('meta_description', t('seo.home_meta_description', 'Custom tailoring, made-to-measure garments and ready-to-wear pieces — hand-stitched by master tailors and delivered to your door.'))

@section('content')
    {{-- Hero --}}
    <section class="bg-brand-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 md:py-28 grid md:grid-cols-2 gap-10 items-center">
            <div>
                <p class="text-gold-400 font-semibold tracking-wide uppercase text-sm mb-4">{{ t('home.hero_kicker', 'Master Tailors Since Generations') }}</p>
                <h1 class="font-display text-4xl md:text-5xl font-bold leading-tight mb-5">{{ $heroTitle }}</h1>
                <p class="text-brand-100 text-lg mb-8 max-w-lg">{{ $heroSubtitle }}</p>
                <div class="flex flex-wrap gap-4">
                    <a href="{{ route('shop.index') }}" class="bg-gold-500 text-brand-950 font-semibold px-6 py-3 rounded-lg hover:bg-gold-400 transition-colors">
                        {{ t('home.cta_shop_collection', 'Shop Collection') }}
                    </a>
                    <a href="{{ route('shop.index', ['type' => 'custom']) }}" class="border border-white/40 text-white font-semibold px-6 py-3 rounded-lg hover:bg-white/10 transition-colors">
                        {{ t('home.cta_custom_tailoring', 'Custom Tailoring') }}
                    </a>
                </div>
            </div>
            <div class="hidden md:grid grid-cols-2 gap-4">
                <div class="bg-brand-800 rounded-2xl p-6 border border-white/10">
                    <p class="text-3xl font-bold text-gold-400 mb-1">100%</p>
                    <p class="text-sm text-brand-100">{{ t('home.stat_stitching', 'Hand-finished stitching') }}</p>
                </div>
                <div class="bg-brand-800 rounded-2xl p-6 border border-white/10 mt-8">
                    <p class="text-3xl font-bold text-gold-400 mb-1">{{ t('home.stat_custom', 'Custom') }}</p>
                    <p class="text-sm text-brand-100">{{ t('home.stat_measurements', 'Made to your measurements') }}</p>
                </div>
                <div class="bg-brand-800 rounded-2xl p-6 border border-white/10 -mt-8">
                    <p class="text-3xl font-bold text-gold-400 mb-1">{{ t('home.stat_days', '2–5 days') }}</p>
                    <p class="text-sm text-brand-100">{{ t('home.stat_tailoring_time', 'Typical tailoring time') }}</p>
                </div>
                <div class="bg-brand-800 rounded-2xl p-6 border border-white/10">
                    <p class="text-3xl font-bold text-gold-400 mb-1">{{ t('home.stat_delivery', 'Delivery') }}</p>
                    <p class="text-sm text-brand-100">{{ t('home.stat_doorstep', 'To your doorstep') }}</p>
                </div>
            </div>
        </div>
    </section>

    {{-- Categories --}}
    @if($categories->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <h2 class="font-display text-2xl md:text-3xl font-bold text-brand-900 mb-8">{{ t('home.browse_categories', 'Browse Categories') }}</h2>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                @foreach($categories as $category)
                    <a href="{{ route('shop.index', ['category' => $category->slug]) }}"
                       class="group relative rounded-xl overflow-hidden aspect-square bg-gray-100 border border-gray-200">
                        <img src="{{ $category->image ? asset('storage/' . $category->image) : '' }}" alt="{{ $category->name }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/10 to-transparent"></div>
                        <div class="absolute bottom-0 p-3 w-full">
                            <p class="text-white font-semibold text-sm leading-tight">{{ $category->name }}</p>
                            <p class="text-white/70 text-xs">{{ $category->products_count }} {{ $category->products_count === 1 ? t('common.item', 'item') : t('common.items', 'items') }}</p>
                        </div>
                    </a>
                @endforeach
            </div>
        </section>
    @endif

    {{-- Featured products --}}
    @if($featured->isNotEmpty())
        <section class="bg-gray-50 py-16">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex items-center justify-between mb-8">
                    <h2 class="font-display text-2xl md:text-3xl font-bold text-brand-900">{{ t('home.featured_pieces', 'Featured Pieces') }}</h2>
                    <a href="{{ route('shop.index') }}" class="text-sm font-medium text-brand-700 hover:underline">{{ t('common.view_all', 'View all') }} &rarr;</a>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4 md:gap-6">
                    @foreach($featured as $product)
                        <x-product-card :product="$product" />
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- How it works --}}
    <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <h2 class="font-display text-2xl md:text-3xl font-bold text-brand-900 mb-10 text-center">{{ t('home.how_it_works', 'How Custom Tailoring Works') }}</h2>
        <div class="grid md:grid-cols-4 gap-6">
            <div class="text-center">
                <div class="w-12 h-12 mx-auto rounded-full bg-brand-100 text-brand-800 font-bold flex items-center justify-center mb-3">1</div>
                <h3 class="font-semibold text-gray-900 mb-1">{{ t('home.step1_title', 'Choose a design') }}</h3>
                <p class="text-sm text-gray-600">{{ t('home.step1_text', 'Pick a made-to-measure piece from our collection.') }}</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 mx-auto rounded-full bg-brand-100 text-brand-800 font-bold flex items-center justify-center mb-3">2</div>
                <h3 class="font-semibold text-gray-900 mb-1">{{ t('home.step2_title', 'Enter measurements') }}</h3>
                <p class="text-sm text-gray-600">{{ t('home.step2_text', 'Add your measurements at checkout — we guide you.') }}</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 mx-auto rounded-full bg-brand-100 text-brand-800 font-bold flex items-center justify-center mb-3">3</div>
                <h3 class="font-semibold text-gray-900 mb-1">{{ t('home.step3_title', 'We stitch') }}</h3>
                <p class="text-sm text-gray-600">{{ t('home.step3_text', 'Our master tailors cut and sew your garment by hand.') }}</p>
            </div>
            <div class="text-center">
                <div class="w-12 h-12 mx-auto rounded-full bg-brand-100 text-brand-800 font-bold flex items-center justify-center mb-3">4</div>
                <h3 class="font-semibold text-gray-900 mb-1">{{ t('home.step4_title', 'Doorstep delivery') }}</h3>
                <p class="text-sm text-gray-600">{{ t('home.step4_text', 'Receive your finished garment, fitted to perfection.') }}</p>
            </div>
        </div>
    </section>

    {{-- New arrivals --}}
    @if($newArrivals->isNotEmpty())
        <section class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 pb-16">
            <div class="flex items-center justify-between mb-8">
                <h2 class="font-display text-2xl md:text-3xl font-bold text-brand-900">{{ t('home.new_arrivals', 'New Arrivals') }}</h2>
                <a href="{{ route('shop.index') }}" class="text-sm font-medium text-brand-700 hover:underline">{{ t('common.view_all', 'View all') }} &rarr;</a>
            </div>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
                @foreach($newArrivals as $product)
                    <x-product-card :product="$product" />
                @endforeach
            </div>
        </section>
    @endif
@endsection
