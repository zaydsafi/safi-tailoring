@extends('layouts.shop')

@section('title', t('seo.forgot_password_title', 'Forgot Password') . ' — ' . $shopSettings['shop_name'])

@section('robots', 'noindex, nofollow')

@section('content')
    @php($waSupport = \App\Support\WhatsApp::shopLink(t('auth.whatsapp_reset_help', 'Hello! I need help resetting my account password.')))
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="bg-white border border-gray-200 rounded-xl p-8">
            <h1 class="font-display text-2xl font-bold text-brand-900 mb-2 text-center">{{ t('auth.forgot_password_heading', 'Forgot Password?') }}</h1>
            <p class="text-sm text-gray-600 text-center mb-6">{{ t('auth.forgot_password_intro', 'Enter the email address on your account and we will send you a link to choose a new password.') }}</p>

            <form method="POST" action="{{ route('password.email') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-1">{{ t('auth.email', 'Email') }}</label>
                    <input type="email" name="email" id="email" value="{{ old('email') }}" required autofocus
                           class="w-full rounded-lg border-gray-300 text-sm focus:border-brand-500 focus:ring-brand-500">
                </div>
                <button type="submit" class="w-full bg-brand-800 text-white font-medium py-3 rounded-lg hover:bg-brand-700">{{ t('auth.email_reset_link', 'Email Reset Link') }}</button>
            </form>

            <p class="text-sm text-gray-600 text-center mt-6">
                {{ t('auth.remembered_it', 'Remembered it?') }}
                <a href="{{ route('login') }}" class="text-brand-700 font-medium hover:underline">{{ t('auth.sign_in_link', 'Sign in') }}</a>
            </p>
            @if($waSupport)
                <p class="text-xs text-gray-500 text-center mt-3">
                    {{ t('auth.no_email_access', 'No access to that email?') }} <a href="{{ $waSupport }}" target="_blank" rel="noopener" class="font-medium text-green-700 hover:underline">{{ t('auth.message_on_whatsapp', 'Message us on WhatsApp') }}</a> {{ t('auth.whatsapp_help_suffix', 'and we will help you.') }}
                </p>
            @endif
        </div>
    </div>
@endsection
