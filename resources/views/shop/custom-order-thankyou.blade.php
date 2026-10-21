@extends('layouts.shop')

@section('title', t('seo.custom_order_thankyou_title', 'Custom Order Request Received') . ' — ' . $shopSettings['shop_name'])
@section('meta_description', t('seo.custom_order_meta_description', 'Tell us what you need made to measure. Our tailoring team confirms the details over WhatsApp or phone, then we deliver to your door.'))

@section('robots', 'noindex, nofollow')

@section('content')

    <section class="relative overflow-hidden bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 text-white">
        <div class="absolute inset-0 opacity-10 pointer-events-none"
             style="background-image: radial-gradient(circle at 75% 15%, #c08f2f 0, transparent 40%), radial-gradient(circle at 15% 80%, #629a93 0, transparent 35%);"></div>

        <div class="relative max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16 sm:py-20 text-center">
            <div class="mx-auto mb-6 flex h-16 w-16 items-center justify-center rounded-full bg-gold-500/15 ring-1 ring-gold-400/40">
                <svg class="h-8 w-8 text-gold-300" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m4.5 12.75 6 6 9-13.5"/></svg>
            </div>
            <p class="text-gold-300 font-semibold tracking-widest uppercase text-xs mb-3">{{ t('custom_order.hero_kicker', 'Made to measure — by hand') }}</p>
            <h1 class="font-display text-3xl sm:text-4xl font-bold mb-4">{{ t('custom_order.thankyou_title', 'Your custom order request is in') }}</h1>
            <p class="text-brand-100 text-base sm:text-lg leading-relaxed max-w-xl mx-auto">{{ t('custom_order.thankyou_body', 'Thank you! Our tailoring team will review your request and follow up to confirm the details and measurements — usually within a day.') }}</p>
            <div class="mt-6 inline-flex items-center gap-2 rounded-full border border-white/15 bg-white/5 px-4 py-2 text-sm">
                <span class="text-brand-200">{{ t('custom_order.thankyou_reference', 'Your request reference') }}:</span>
                <span class="font-bold text-gold-300">#{{ $requestRecord->id }}</span>
            </div>
        </div>
        <svg class="absolute bottom-0 start-0 w-full h-10 text-white" preserveAspectRatio="none" viewBox="0 0 1440 48" fill="currentColor"><path d="M0 48h1440V24c-160 12-360 18-560 12S220 8 0 24v24Z"/></svg>
    </section>

    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <div class="rounded-2xl border border-brand-100 bg-white shadow-sm p-6 sm:p-8">
            <h2 class="font-semibold text-brand-900 mb-4">{{ t('custom_order.thankyou_next_title', 'What happens next') }}</h2>
            <ol class="space-y-4 text-sm text-brand-700">
                <li class="flex gap-3">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-800">1</span>
                    <p class="leading-relaxed">{{ t('custom_order.thankyou_next1', 'We review your request and reach out over WhatsApp or phone (usually within a day).') }}</p>
                </li>
                <li class="flex gap-3">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-800">2</span>
                    <p class="leading-relaxed">{{ t('custom_order.thankyou_next2', 'We agree on the design, fabric, price and delivery time together.') }}</p>
                </li>
                <li class="flex gap-3">
                    <span class="flex h-7 w-7 shrink-0 items-center justify-center rounded-full bg-brand-100 text-xs font-bold text-brand-800">3</span>
                    <p class="leading-relaxed">{{ t('custom_order.thankyou_next3', 'Our tailors make your garment and deliver it to your door.') }}</p>
                </li>
            </ol>

            <div class="mt-8 flex flex-wrap gap-3">
                @php $waThanks = \App\Support\WhatsApp::shopLink(t('custom_order.whatsapp_fitting_intro', 'Hello! I just submitted a custom order request.') . ' (' . t('custom_order.thankyou_reference', 'Your request reference') . ': #' . $requestRecord->id . ')'); @endphp
                @if ($waThanks)
                    <a href="{{ $waThanks }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 rounded-xl bg-green-600 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        {{ t('custom_order.thankyou_contact', 'Have questions? Chat with us') }}
                    </a>
                @endif
                <a href="{{ route('home') }}" class="inline-flex items-center gap-2 rounded-xl border border-brand-200 px-5 py-2.5 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">
                    {{ t('nav.home', 'Back to Home') }}
                </a>
                @auth
                    <a href="{{ route('account.orders') }}" class="inline-flex items-center gap-2 rounded-xl border border-brand-200 px-5 py-2.5 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">
                        {{ t('nav.my_orders', 'My Orders') }}
                    </a>
                @endauth
            </div>
        </div>
    </div>
@endsection
