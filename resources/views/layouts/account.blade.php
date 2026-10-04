@extends('layouts.shop')

@section('robots', 'noindex, nofollow')

@section('content')
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
        <div class="grid md:grid-cols-4 gap-8">
            <aside class="md:col-span-1">
                <nav class="bg-white border border-gray-200 rounded-xl p-4 space-y-1 text-sm font-medium">
                    <a href="{{ route('account.orders') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('account.orders*') ? 'bg-brand-100 text-brand-900' : 'text-gray-600 hover:bg-gray-100' }}">{{ t('account.my_orders', 'My Orders') }}</a>
                    <a href="{{ route('account.measurements') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('account.measurements*') ? 'bg-brand-100 text-brand-900' : 'text-gray-600 hover:bg-gray-100' }}">{{ t('account.my_measurements', 'My Measurements') }}</a>
                    <a href="{{ route('account.profile') }}" class="block px-3 py-2 rounded-lg {{ request()->routeIs('account.profile*') ? 'bg-brand-100 text-brand-900' : 'text-gray-600 hover:bg-gray-100' }}">{{ t('account.profile', 'Profile') }}</a>
                </nav>
            </aside>

            <div class="md:col-span-3">
                @yield('account-content')
            </div>
        </div>
    </div>
@endsection
