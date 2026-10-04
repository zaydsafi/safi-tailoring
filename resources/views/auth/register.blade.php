@extends('layouts.shop')

@section('title', t('seo.register_title', 'Create Account') . ' — ' . $shopSettings['shop_name'])

@section('robots', 'noindex, nofollow')

@section('content')
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="bg-white border border-gray-200 rounded-xl p-8">
            <h1 class="font-display text-2xl font-bold text-brand-900 mb-6 text-center">{{ t('auth.create_account', 'Create Account') }}</h1>

            <form method="POST" action="{{ route('register.store') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-1">{{ t('auth.full_name', 'Full name') }}</label>
                    <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ t('auth.email', 'Email') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-1">{{ t('auth.whatsapp_number', 'WhatsApp number') }}</label>
                    <input type="tel" name="phone" id="phone" value="{{ old('phone') }}" required placeholder="07XX XXX XXXX"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <p class="text-xs text-gray-500 mt-1">{{ t('auth.phone_hint', 'Used to sign in and receive order updates on WhatsApp.') }}</p>
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ t('auth.password', 'Password') }}</label>
                    <input type="password" name="password" id="password" required minlength="8"
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <p class="text-xs text-gray-500 mt-1">{{ t('auth.password_hint', 'Minimum 8 characters.') }}</p>
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ t('auth.confirm_password', 'Confirm password') }}</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <button type="submit" class="w-full bg-brand-800 text-white font-medium py-3 rounded-lg hover:bg-brand-700">{{ t('auth.create_account', 'Create Account') }}</button>
            </form>

            <p class="text-sm text-gray-600 text-center mt-6">
                {{ t('auth.already_have_account', 'Already have an account?') }}
                <a href="{{ route('login') }}" class="text-brand-700 font-medium hover:underline">{{ t('auth.sign_in_link', 'Sign in') }}</a>
            </p>
        </div>
    </div>
@endsection
