@extends('layouts.shop')

@section('title', t('errors.server_error_title', 'Something Went Wrong') . ' — ' . $shopSettings['shop_name'])

@section('robots', 'noindex, nofollow')

@section('content')
    <div class="max-w-2xl mx-auto px-4 py-24 text-center">
        <p class="font-display text-7xl font-black text-brand-200 select-none">500</p>
        <h1 class="font-display text-3xl font-bold text-brand-900 mt-2 mb-4">{{ t('errors.server_error_heading', 'Something went wrong on our side') }}</h1>
        <p class="text-gray-600 mb-8">{{ t('errors.server_error_body', 'An unexpected error occurred. Our team has been notified — please try again in a moment.') }}</p>
        <div class="flex flex-wrap justify-center gap-4">
            <a href="{{ route('home') }}" class="bg-brand-800 text-white font-medium px-6 py-3 rounded-lg hover:bg-brand-700 transition-colors">{{ t('errors.go_home', 'Go to Homepage') }}</a>
            <a href="{{ route('contact') }}" class="border border-gray-300 text-gray-700 font-medium px-6 py-3 rounded-lg hover:bg-gray-50 transition-colors">{{ t('errors.contact_us', 'Contact Us') }}</a>
        </div>
    </div>
@endsection
