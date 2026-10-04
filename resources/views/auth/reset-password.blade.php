@extends('layouts.shop')

@section('title', t('seo.reset_password_title', 'Reset Password') . ' — ' . $shopSettings['shop_name'])

@section('robots', 'noindex, nofollow')

@section('content')
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="bg-white border border-gray-200 rounded-xl p-8">
            <h1 class="font-display text-2xl font-bold text-brand-900 mb-2 text-center">{{ t('auth.choose_new_password', 'Choose a New Password') }}</h1>
            <p class="text-sm text-gray-600 text-center mb-6">{{ t('auth.reset_password_intro', 'Set a new password for your account below.') }}</p>

            <form method="POST" action="{{ route('password.update') }}" class="space-y-4">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ t('auth.email', 'Email') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $email) }}" required
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-1">{{ t('auth.new_password', 'New password') }}</label>
                    <input type="password" name="password" id="password" required minlength="8" autofocus
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                    <p class="text-xs text-gray-500 mt-1">{{ t('auth.password_hint', 'Minimum 8 characters.') }}</p>
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-1">{{ t('auth.confirm_new_password', 'Confirm new password') }}</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" required
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <button type="submit" class="w-full bg-brand-800 text-white font-medium py-3 rounded-lg hover:bg-brand-700">{{ t('auth.reset_password_button', 'Reset Password') }}</button>
            </form>

            <p class="text-sm text-gray-600 text-center mt-6">
                <a href="{{ route('login') }}" class="text-brand-700 font-medium hover:underline">{{ t('auth.back_to_sign_in', 'Back to sign in') }}</a>
            </p>
        </div>
    </div>
@endsection
