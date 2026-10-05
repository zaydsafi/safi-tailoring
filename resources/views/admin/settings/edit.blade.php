@extends('layouts.admin')

@section('title', t('admin.settings.title', 'Settings'))

@section('content')
    <div class="max-w-3xl rounded-2xl border border-brand-100 bg-white p-6 shadow-sm sm:p-8">
        <form method="POST" action="{{ route('admin.settings.update') }}" class="space-y-8">
            @csrf
            @method('PUT')

            <section>
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-brand-400">{{ t('admin.settings.shop_information', 'Shop Information') }}</h3>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.settings.shop_name', 'Shop Name') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="shop_name" value="{{ old('shop_name', $settings['shop_name']) }}" required
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('shop_name') border-red-400 @enderror">
                        @error('shop_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.settings.tagline', 'Tagline') }}</label>
                        <input type="text" name="shop_tagline" value="{{ old('shop_tagline', $settings['shop_tagline']) }}"
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('shop_tagline') border-red-400 @enderror">
                        @error('shop_tagline')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.common.phone', 'Phone') }}</label>
                        <input type="text" name="shop_phone" value="{{ old('shop_phone', $settings['shop_phone']) }}"
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('shop_phone') border-red-400 @enderror">
                        @error('shop_phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.common.email', 'Email') }}</label>
                        <input type="email" name="shop_email" value="{{ old('shop_email', $settings['shop_email']) }}"
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('shop_email') border-red-400 @enderror">
                        @error('shop_email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.common.address', 'Address') }}</label>
                        <textarea name="shop_address" rows="2"
                                  class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('shop_address') border-red-400 @enderror">{{ old('shop_address', $settings['shop_address']) }}</textarea>
                        @error('shop_address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section>
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-brand-400">{{ t('admin.settings.currency_delivery', 'Currency & Delivery') }}</h3>
                <div class="grid gap-5 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.settings.currency_symbol', 'Currency Symbol') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="currency_symbol" value="{{ old('currency_symbol', $settings['currency_symbol']) }}" required maxlength="10"
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('currency_symbol') border-red-400 @enderror">
                        @error('currency_symbol')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.settings.delivery_fee', 'Delivery Fee') }}</label>
                        <input type="number" step="0.01" min="0" name="delivery_fee" value="{{ old('delivery_fee', $settings['delivery_fee']) }}"
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('delivery_fee') border-red-400 @enderror">
                        @error('delivery_fee')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.settings.free_delivery_over', 'Free Delivery Over') }}</label>
                        <input type="number" step="0.01" min="0" name="free_delivery_over" value="{{ old('free_delivery_over', $settings['free_delivery_over']) }}"
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('free_delivery_over') border-red-400 @enderror">
                        @error('free_delivery_over')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section>
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-brand-400">{{ t('admin.settings.homepage', 'Homepage') }}</h3>
                <div class="grid gap-5">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.settings.hero_title', 'Hero Title') }}</label>
                        <input type="text" name="hero_title" value="{{ old('hero_title', $settings['hero_title']) }}"
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('hero_title') border-red-400 @enderror">
                        @error('hero_title')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.settings.hero_subtitle', 'Hero Subtitle') }}</label>
                        <textarea name="hero_subtitle" rows="2"
                                  class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('hero_subtitle') border-red-400 @enderror">{{ old('hero_subtitle', $settings['hero_subtitle']) }}</textarea>
                        @error('hero_subtitle')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.settings.announcement_bar', 'Announcement Bar') }}</label>
                        <input type="text" name="announcement" value="{{ old('announcement', $settings['announcement']) }}"
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('announcement') border-red-400 @enderror">
                        @error('announcement')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section>
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-brand-400">{{ t('admin.settings.social_messaging', 'Social & Messaging') }}</h3>
                <div class="grid gap-5 sm:grid-cols-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.settings.facebook_url', 'Facebook URL') }}</label>
                        <input type="url" name="facebook_url" value="{{ old('facebook_url', $settings['facebook_url']) }}" placeholder="https://facebook.com/…"
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('facebook_url') border-red-400 @enderror">
                        @error('facebook_url')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.settings.instagram_url', 'Instagram URL') }}</label>
                        <input type="url" name="instagram_url" value="{{ old('instagram_url', $settings['instagram_url']) }}" placeholder="https://instagram.com/…"
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('instagram_url') border-red-400 @enderror">
                        @error('instagram_url')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.settings.whatsapp_number', 'WhatsApp Number') }}</label>
                        <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number', $settings['whatsapp_number']) }}" placeholder="+93…"
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('whatsapp_number') border-red-400 @enderror">
                        @error('whatsapp_number')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.settings.country_code', 'Country Code') }}</label>
                        <input type="text" name="whatsapp_country_code" value="{{ old('whatsapp_country_code', $settings['whatsapp_country_code']) }}" placeholder="93"
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('whatsapp_country_code') border-red-400 @enderror">
                        <p class="mt-1 text-xs text-brand-400">{{ t('admin.settings.country_code_hint', 'Used to turn local numbers (0700…) into international ones (93700…).') }}</p>
                        @error('whatsapp_country_code')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section>
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-brand-400">{{ t('admin.settings.hesabpay_payments', 'HesabPay Payments') }}</h3>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="flex items-center gap-2 text-sm font-medium text-brand-900">
                            <input type="checkbox" name="hesabpay_enabled" value="1" @checked(old('hesabpay_enabled', $settings['hesabpay_enabled']) === '1')
                                   class="h-4 w-4 rounded border-brand-200 text-teal-600 focus:ring-teal-600/20">
                            {{ t('admin.settings.enable_hesabpay', 'Enable HesabPay as a checkout payment method') }}
                        </label>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.settings.environment', 'Environment') }}</label>
                        <select name="hesabpay_environment"
                                class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('hesabpay_environment') border-red-400 @enderror">
                            <option value="production" @selected(old('hesabpay_environment', $settings['hesabpay_environment']) === 'production')>{{ t('admin.settings.environment_production', 'Production (live)') }}</option>
                            <option value="sandbox" @selected(old('hesabpay_environment', $settings['hesabpay_environment']) === 'sandbox')>{{ t('admin.settings.environment_sandbox', 'Sandbox (testing)') }}</option>
                        </select>
                        @error('hesabpay_environment')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.settings.api_key', 'API Key') }}</label>
                        <input type="text" name="hesabpay_api_key" value="{{ old('hesabpay_api_key', $settings['hesabpay_api_key']) }}" placeholder="{{ t('admin.settings.api_key_placeholder', 'From your HesabPay merchant account') }}"
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('hesabpay_api_key') border-red-400 @enderror">
                        <p class="mt-1 text-xs text-brand-400">{{ t('admin.settings.api_key_hint', 'Keep this private. Rotate it in your HesabPay dashboard if it is ever shared.') }}</p>
                        @error('hesabpay_api_key')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2" x-data="{ gen() { const a = new Uint8Array(24); crypto.getRandomValues(a); return Array.from(a, b => b.toString(16).padStart(2, '0')).join(''); } }">
                        <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.settings.webhook_secret', 'Webhook Secret (required for webhooks)') }}</label>
                        <div class="flex gap-2">
                            <input type="text" name="hesabpay_webhook_secret" x-ref="secret" value="{{ old('hesabpay_webhook_secret', $settings['hesabpay_webhook_secret']) }}" placeholder="{{ t('admin.settings.webhook_secret_placeholder', 'Generate one, then paste the same value into HesabPay') }}"
                                   class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('hesabpay_webhook_secret') border-red-400 @enderror">
                            <button type="button" @click="$refs.secret.value = gen()"
                                    class="shrink-0 rounded-xl border border-brand-200 px-4 py-2.5 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">{{ t('admin.settings.generate', 'Generate') }}</button>
                        </div>
                        <p class="mt-1 text-xs text-brand-400">
                            {{ t('admin.settings.webhook_url_label', 'Webhook URL for HesabPay:') }} <code class="rounded bg-brand-50 px-1.5 py-0.5">{{ route('webhooks.hesabpay') }}</code>
                            {{ t('admin.settings.webhook_requests_note', '. Requests must send this value in the') }} <code class="rounded bg-brand-50 px-1.5 py-0.5">X-HesabPay-Secret</code>
                            {{ t('admin.settings.webhook_hmac_note', 'header (or sign the raw body with HMAC-SHA256 as') }} <code class="rounded bg-brand-50 px-1.5 py-0.5">signature</code>{{ t('admin.settings.webhook_pending_note', '). Until a secret is set, webhook calls are rejected — payments are then confirmed by the checkout redirect only.') }}
                        </p>
                        @error('hesabpay_webhook_secret')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <section>
                <h3 class="mb-4 text-sm font-bold uppercase tracking-wide text-brand-400">{{ t('admin.settings.whatsapp_notifications', 'WhatsApp Notifications') }}</h3>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div class="sm:col-span-2">
                        <label class="flex items-center gap-2 text-sm font-medium text-brand-900">
                            <input type="checkbox" name="whatsapp_cloud_enabled" value="1" @checked(old('whatsapp_cloud_enabled', $settings['whatsapp_cloud_enabled']) === '1')
                                   class="h-4 w-4 rounded border-brand-200 text-teal-600 focus:ring-teal-600/20">
                            {{ t('admin.settings.enable_whatsapp_cloud', 'Send automatic WhatsApp notifications (Meta Cloud API — free tier)') }}
                        </label>
                        <p class="mt-1 text-xs text-brand-400">
                            {{ t('admin.settings.whatsapp_cloud_fallback_note', 'Without this, customers can still reach you for free through WhatsApp buttons (wa.me links) on every order page.') }}
                        </p>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.settings.phone_number_id', 'Phone Number ID') }}</label>
                        <input type="text" name="whatsapp_phone_number_id" value="{{ old('whatsapp_phone_number_id', $settings['whatsapp_phone_number_id']) }}" placeholder="{{ t('admin.settings.phone_number_id_placeholder', 'From Meta WhatsApp Business setup') }}"
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('whatsapp_phone_number_id') border-red-400 @enderror">
                        @error('whatsapp_phone_number_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.settings.access_token', 'Access Token') }}</label>
                        <input type="text" name="whatsapp_cloud_token" value="{{ old('whatsapp_cloud_token', $settings['whatsapp_cloud_token']) }}" placeholder="{{ t('admin.settings.access_token_placeholder', 'Permanent access token') }}"
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('whatsapp_cloud_token') border-red-400 @enderror">
                        @error('whatsapp_cloud_token')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </section>

            <button type="submit" class="rounded-xl bg-teal-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-teal-800">{{ t('admin.settings.save', 'Save Settings') }}</button>
        </form>
    </div>
@endsection
