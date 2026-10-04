@extends('layouts.shop')

@section('title', t('seo.login_title', 'Sign In') . ' — ' . $shopSettings['shop_name'])

@section('robots', 'noindex, nofollow')

@section('content')
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="bg-white border border-gray-200 rounded-xl p-8">
            <h1 class="font-display text-2xl font-bold text-brand-900 mb-6 text-center">{{ t('auth.sign_in', 'Sign In') }}</h1>

            <form method="POST" action="{{ route('login.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="login" class="block text-sm font-medium text-gray-700 mb-1">{{ t('auth.email_or_phone', 'Email or WhatsApp number') }}</label>
                    <input type="text" name="login" id="login" value="{{ old('login') }}" required autofocus
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ t('auth.password', 'Password') }}</label>
                    <input type="password" name="password" id="password" required
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <p class="text-right mt-1">
                        <a href="{{ route('password.request') }}" class="text-xs font-medium text-brand-700 hover:underline">{{ t('auth.forgot_password', 'Forgot password?') }}</a>
                    </p>
                </div>
                <label class="flex items-center gap-2 text-sm text-gray-600">
                    <input type="checkbox" name="remember" class="rounded border-gray-300 text-brand-600 focus:ring-brand-500">
                    {{ t('auth.remember_me', 'Remember me') }}
                </label>
                <button type="submit" class="w-full bg-brand-800 text-white font-medium py-3 rounded-lg hover:bg-brand-700">{{ t('auth.sign_in', 'Sign In') }}</button>
            </form>

            <p class="text-sm text-gray-600 text-center mt-6">
                {{ t('auth.new_customer', 'New customer?') }}
                <a href="{{ route('register') }}" class="text-brand-700 font-medium hover:underline">{{ t('auth.create_an_account', 'Create an account') }}</a>
            </p>
            <p class="text-xs text-gray-500 text-center mt-3">
                {{ t('auth.admin_question', 'Admin?') }} <a href="{{ route('admin.login') }}" class="hover:underline">{{ t('auth.admin_sign_in_link', 'Sign in to the admin panel') }}</a>
            </p>
        </div>
    </div>
@endsection
