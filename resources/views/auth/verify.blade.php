@extends('layouts.shop')

@section('title', t('auth.verify_title', 'Verify Your Account') . ' — ' . $shopSettings['shop_name'])

@section('robots', 'noindex, nofollow')

@section('content')
    <div class="max-w-md mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="bg-white border border-gray-200 rounded-xl p-8">
            <h1 class="font-display text-2xl font-bold text-brand-900 mb-2 text-center">{{ t('auth.verify_title', 'Verify Your Account') }}</h1>
            <p class="text-sm text-gray-600 mb-6 text-center">
                {{ t('flash.otp_sent', 'We sent a 6-digit code — enter it below to finish signing up.') }}
            </p>

            <div class="mb-6 space-y-1 text-sm text-gray-700">
                @foreach ($channels as $channel)
                    @if ($channel === 'whatsapp')
                        <p>📲 {{ t('otp.channel_whatsapp', 'WhatsApp: sent to your number') }}</p>
                    @else
                        <p>✉️ {{ t('otp.channel_email', 'Email: sent to :email', ['email' => request()->user()->email]) }}</p>
                    @endif
                @endforeach
            </div>

            @if (session('success') || session('error'))
                <div class="mb-4 p-3 rounded-lg {{ session('success') ? 'bg-green-50 text-green-800' : 'bg-red-50 text-red-800' }} text-sm">
                    {{ session('success') ?? session('error') }}
                </div>
            @endif

            <form method="POST" action="{{ route('verification.check') }}" class="space-y-4">
                @csrf
                <div>
                    <label for="code" class="block text-sm font-medium text-gray-700 mb-1">{{ t('auth.verify_code', 'Verification code') }}</label>
                    <input type="text" name="code" id="code" inputmode="numeric" pattern="[0-9]*" maxlength="8"
                           autocomplete="one-time-code" required autofocus
                           class="w-full tracking-[0.5em] text-center font-mono rounded-lg border-gray-300 text-lg focus:border-brand-500 focus:ring-brand-500"
                           placeholder="------">
                    <p class="text-xs text-gray-500 mt-1">{{ t('auth.verify_code_hint', 'Valid for 10 minutes.') }}</p>
                </div>
                <button type="submit" class="w-full bg-brand-800 text-white font-medium py-3 rounded-lg hover:bg-brand-700">
                    {{ t('auth.verify_submit', 'Verify & Continue') }}
                </button>
            </form>

            <form method="POST" action="{{ route('verification.resend') }}" x-data="{ idle: {{ (int) $cooldown }} }"
                  x-on:submit="idle > 0 ? $event.preventDefault() : null" class="mt-4" x-init="setInterval(() => idle = Math.max(0, idle - 1), 1000)">
                @csrf
                <button type="submit" class="w-full py-3 rounded-lg border font-medium
                        {{ $cooldown > 0
                            ? 'border-gray-300 text-gray-500'
                            : 'border-brand-700 text-brand-700 hover:bg-brand-50' }}">
                    @php($resendWait = t('auth.verify_resend_wait', 'Resend code in :seconds s', ['seconds' => (int) $cooldown]))
                    <span x-show="idle > 0" x-text="{{ (int) $cooldown > 0 ? json_encode($resendWait) : '\"\"' }}">{{ $resendWait }}</span>
                    <span x-show="idle <= 0">{{ t('auth.verify_resend', 'Resend code') }}</span>
                </button>
            </form>

            <p class="text-sm text-gray-600 text-center mt-6">
                {{ t('auth.verify_unexpected_problems', 'Problems? ') }}
                <a href="{{ \App\Support\WhatsApp::shopLink(t('otp.support_message', 'Hi, I need help verifying my account.')) ?? '#' }}" class="text-brand-700 font-medium hover:underline">
                    {{ t('auth.verify_contact', 'Contact us on WhatsApp') }}
                </a>
            </p>
        </div>
    </div>
@endsection
