@extends('layouts.shop')

@section('title', t('seo.about_title', 'About') . ' — ' . $shopSettings['shop_name'])

@section('meta_description', t('seo.about_meta_description', 'A family tailoring house crafting made-to-measure garments by hand for generations. Browse our collection or order custom tailoring with doorstep delivery.'))

@section('content')
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <h1 class="font-display text-3xl font-bold text-brand-900 mb-6">{{ t('about.heading', 'About') }} {{ $shopSettings['shop_name'] }}</h1>

        <div class="prose prose-gray max-w-none text-gray-600 space-y-4">
            <p>
                {{ $shopSettings['shop_name'] }} {{ t('about.intro_1', 'is a family tailoring house built on a simple belief: a garment should fit the person, not the other way around. For generations our master tailors have cut, stitched, and finished garments by hand — from everyday shalwar kameez to bridal wear and formal suits.') }}
            </p>
            <p>
                {{ t('about.intro_2', 'Today we combine that craft with the convenience of online ordering. Browse our ready-to-wear collection, or choose a made-to-measure piece and enter your measurements at checkout. Every custom order is confirmed by phone before our tailors begin, and delivered to your door when it is ready.') }}
            </p>
            <p>
                {{ t('about.intro_3', 'We also offer professional alteration services — hemming, resizing, and pressing — so the clothes you already own can fit perfectly too.') }}
            </p>
        </div>

        <div class="grid sm:grid-cols-3 gap-4 mt-10">
            <div class="bg-brand-50 border border-brand-100 rounded-xl p-5 text-center">
                <p class="font-display text-2xl font-bold text-brand-800">{{ t('about.feature_handcrafted_line1', 'Hand') }}<br>{{ t('about.feature_handcrafted_line2', 'Crafted') }}</p>
                <p class="text-sm text-gray-600 mt-2">{{ t('about.feature_handcrafted_desc', 'Every stitch finished by experienced hands') }}</p>
            </div>
            <div class="bg-brand-50 border border-brand-100 rounded-xl p-5 text-center">
                <p class="font-display text-2xl font-bold text-brand-800">{{ t('about.feature_fit_line1', 'Perfect') }}<br>{{ t('about.feature_fit_line2', 'Fit') }}</p>
                <p class="text-sm text-gray-600 mt-2">{{ t('about.feature_fit_desc', 'Made to your exact measurements') }}</p>
            </div>
            <div class="bg-brand-50 border border-brand-100 rounded-xl p-5 text-center">
                <p class="font-display text-2xl font-bold text-brand-800">{{ t('about.feature_delivery_line1', 'Doorstep') }}<br>{{ t('about.feature_delivery_line2', 'Delivery') }}</p>
                <p class="text-sm text-gray-600 mt-2">{{ t('about.feature_delivery_desc', 'From our workshop straight to you') }}</p>
            </div>
        </div>

        <div class="mt-10 text-center">
            <a href="{{ route('shop.index') }}" class="inline-block bg-brand-800 text-white font-medium px-8 py-3 rounded-lg hover:bg-brand-700">{{ t('about.browse_collection', 'Browse Our Collection') }}</a>
        </div>
    </div>
@endsection
