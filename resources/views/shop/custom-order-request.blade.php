@extends('layouts.shop')

@section('title', t('seo.custom_order_title', 'Custom Order Request') . ' — ' . $shopSettings['shop_name'])
@section('meta_description', t('seo.custom_order_meta_description', 'Tell us what you need made to measure. Our tailoring team confirms the details over WhatsApp or phone, then we deliver to your door.'))

@section('robots', 'noindex, nofollow')

@section('content')

    {{-- Hero band --}}
    <section class="relative overflow-hidden bg-gradient-to-br from-brand-950 via-brand-900 to-brand-800 text-white">
        <div class="absolute inset-0 opacity-10 pointer-events-none"
             style="background-image: radial-gradient(circle at 80% 20%, #c08f2f 0, transparent 42%), radial-gradient(circle at 12% 85%, #629a93 0, transparent 38%);"></div>

        <div class="relative max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-14 sm:py-16">
            <div class="max-w-2xl">
                <p class="text-gold-300 font-semibold tracking-widest uppercase text-xs mb-3">{{ t('custom_order.hero_kicker', 'Made to measure — by hand') }}</p>
                <h1 class="font-display text-3xl sm:text-4xl font-bold leading-tight mb-4">{{ t('custom_order.hero_title', 'Tell us what you need, and our tailors will make it.')}}</h1>
                <p class="text-brand-100 text-base sm:text-lg leading-relaxed">{{ t('custom_order.hero_subtitle', 'Suits, kameez, dresses, or alterations — describe your garment, add the measurements you have, and we do the rest: fitting advice over WhatsApp or a phone call, hand-made tailoring, and delivery to your door.') }}</p>
            </div>

            <div class="mt-10 grid gap-4 sm:grid-cols-3">
                <div class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/5 backdrop-blur p-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gold-500/15 text-gold-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M2.25 6.75c1.152-.263 2.287-.399 3.5-.399 1.594 0 3.157.241 4.625.687M2.25 6.75v10.5c0 1.207 1.152 2.277 2.75 2.546 1.05.332 2.165.282 3.125-.162M2.25 6.75c3.27 0 5.846.734 7.5 1.75m7.5-1.75c1.305 0 2.613.106 3.875.311a39 39 0 0 1 1.375 7.544c0 1.18-.28 2.296-.789 3.246-1.426-.263-2.892-.35-4.302-.294a38 38 0 0 0-9.163 1.018m6.636-9.421c1.478.448 2.9.878 4.348 1.2"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">{{ t('custom_order.feature_call', 'Personal confirmation call') }}</p>
                        <p class="mt-1 text-xs text-brand-200 leading-relaxed">{{ t('custom_order.feature_call_desc', 'A real person confirms every detail and measurement with you before we cut fabric.') }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/5 backdrop-blur p-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-teal-400/15 text-teal-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 21a8.966 8.966 0 0 0 5.982-2.275M1.5 4.492v10.675a8.25 8.25 0 0 0 8.25 8.25h.378a2.25 2.25 0 0 0 2.184-2.822l-2.239-8.787a1.5 1.5 0 0 0-1.458-1.021L8.25 10.5A2.25 2.25 0 0 1 6 8.25v-.75a2.25 2.25 0 0 1 4.5 0v.75"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">{{ t('custom_order.feature_measure', 'Made to your measurements') }}</p>
                        <p class="mt-1 text-xs text-brand-200 leading-relaxed">{{ t('custom_order.feature_measure_desc', 'Fill in what you know — or leave it blank and we will guide you through it.') }}</p>
                    </div>
                </div>

                <div class="flex items-start gap-3 rounded-2xl border border-white/10 bg-white/5 backdrop-blur p-4">
                    <div class="flex h-10 w-10 shrink-0 items-center justify-center rounded-full bg-gold-400/15 text-gold-300">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 18.75a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h6m-9 0H3.375a1.125 1.125 0 0 1-1.125-1.125V14.25m17.25 4.5a1.5 1.5 0 0 1-3 0m3 0a1.5 1.5 0 0 0-3 0m3 0h1.125c.621 0 1.129-.504 1.09-1.124a17.9 17.9 0 0 0-3.213-9.193 2.056 2.056 0 0 0-1.58-.86H14.25M16.5 18.75h-2.25m0-12H.75v.375"/></svg>
                    </div>
                    <div>
                        <p class="text-sm font-semibold">{{ t('custom_order.feature_delivery', 'Hand-made, then delivered') }}</p>
                        <p class="mt-1 text-xs text-brand-200 leading-relaxed">{{ t('custom_order.feature_delivery_desc', 'Cut and sewn by our master tailors, delivered to your door when it is ready.') }}</p>
                    </div>
                </div>
            </div>
        </div>
        <svg class="absolute bottom-0 start-0 w-full h-10 text-white" preserveAspectRatio="none" viewBox="0 0 1440 48" fill="currentColor"><path d="M0 48h1440V24c-160 12-360 18-560 12S220 8 0 24v24Z"/></svg>
    </section>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid gap-8 lg:grid-cols-3">
        {{-- Form --}}
        <form method="POST" action="{{ route('custom-order.request.store') }}" class="lg:col-span-2 space-y-6">
            @csrf

            {{-- Contact --}}
            <div class="bg-white border border-brand-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-brand-50 bg-brand-50/60">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-800 text-xs font-bold text-white">1</span>
                    <h2 class="font-semibold text-brand-900">{{ t('custom_order.contact_details', 'Contact Details') }}</h2>
                </div>
                <div class="px-6 py-5 grid sm:grid-cols-2 gap-4">
                    <div>
                        <label for="customer_name" class="block text-sm font-medium text-brand-900 mb-1.5">{{ t('custom_order.name', 'Full name') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="customer_name" id="customer_name" value="{{ old('customer_name', auth()->user()->name ?? '') }}" required
                               class="w-full rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 @error('customer_name') border-red-400 @enderror">
                        @error('customer_name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="customer_phone" class="block text-sm font-medium text-brand-900 mb-1.5">{{ t('custom_order.phone', 'Phone') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="customer_phone" id="customer_phone" value="{{ old('customer_phone', auth()->user()->phone ?? '') }}" required placeholder="0700 …"
                               class="w-full rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 @error('customer_phone') border-red-400 @enderror">
                        @error('customer_phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="customer_email" class="block text-sm font-medium text-brand-900 mb-1.5">{{ t('custom_order.email_optional', 'Email (optional)') }}</label>
                        <input type="email" name="customer_email" id="customer_email" value="{{ old('customer_email', auth()->user()->email ?? '') }}"
                               class="w-full rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 @error('customer_email') border-red-400 @enderror">
                        @error('customer_email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div class="sm:col-span-2">
                        <label for="customer_address" class="block text-sm font-medium text-brand-900 mb-1.5">{{ t('custom_order.address', 'Delivery address') }} <span class="text-red-500">*</span></label>
                        <input type="text" name="customer_address" id="customer_address" value="{{ old('customer_address', auth()->user()->address ?? '') }}" required
                               class="w-full rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 @error('customer_address') border-red-400 @enderror">
                        @error('customer_address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="customer_city" class="block text-sm font-medium text-brand-900 mb-1.5">{{ t('custom_order.city_optional', 'City (optional)') }}</label>
                        <input type="text" name="customer_city" id="customer_city" value="{{ old('customer_city', auth()->user()->city ?? '') }}"
                               class="w-full rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 @error('customer_city') border-red-400 @enderror">
                        @error('customer_city')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            {{-- Garment --}}
            <div class="bg-white border border-brand-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-brand-50 bg-brand-50/60">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-800 text-xs font-bold text-white">2</span>
                    <h2 class="font-semibold text-brand-900">{{ t('custom_order.request_details', 'What do you need made?') }}</h2>
                </div>
                <div class="px-6 py-5 space-y-4">
                    <div>
                        <label for="request_description" class="block text-sm font-medium text-brand-900 mb-1.5">{{ t('custom_order.description', 'Describe the garment') }} <span class="text-red-500">*</span></label>
                        <textarea name="request_description" id="request_description" rows="4" required
                                  placeholder="{{ t('custom_order.description_placeholder', 'e.g. Two-piece kameez for a wedding, dark blue, slim cut, with matching shawl…') }}"
                                  class="w-full rounded-xl border border-brand-200 bg-white px-4 py-3 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20 @error('request_description') border-red-400 @enderror">{{ old('request_description') }}</textarea>
                        @error('request_description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="preferred_fabric" class="block text-sm font-medium text-brand-900 mb-1.5">{{ t('custom_order.fabric_optional', 'Preferred fabric (optional)') }}</label>
                        <input type="text" name="preferred_fabric" id="preferred_fabric" value="{{ old('preferred_fabric') }}" placeholder="{{ t('custom_order.fabric_placeholder', 'e.g. cotton, linen, silk, wool…') }}"
                               class="w-full rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20">
                    </div>
                </div>
            </div>

            {{-- Measurements --}}
            <div class="bg-white border border-brand-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-brand-50 bg-brand-50/60">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-800 text-xs font-bold text-white">3</span>
                    <h2 class="font-semibold text-brand-900">{{ t('custom_order.measurements', 'Your measurements (inches)') }}</h2>
                </div>
                <div class="px-6 py-5">
                    <div class="grid grid-cols-2 sm:grid-cols-3 gap-3">
                        @foreach (['chest' => 'measure_chest', 'waist' => 'measure_waist', 'shoulder' => 'measure_shoulder', 'sleeve' => 'measure_sleeve', 'shirt_length' => 'measure_shirt_length'] as $field => $labelKey)
                            <div class="rounded-xl border border-brand-100 bg-brand-50/50 px-3 py-2.5 focus-within:border-brand-400 focus-within:bg-white transition">
                                <label for="measurements_{{ $field }}" class="block text-[11px] font-semibold uppercase tracking-wide text-brand-500 mb-0.5">{{ t('common.' . $labelKey) }}</label>
                                <input type="number" step="0.5" min="1" max="300" name="measurements[{{ $field }}]" id="measurements_{{ $field }}" value="{{ old('measurements.' . $field) }}"
                                       class="w-full bg-transparent text-sm text-brand-900 outline-none" placeholder="—">
                            </div>
                        @endforeach
                    </div>
                    <p class="mt-3 flex items-start gap-2 text-xs text-brand-500 leading-relaxed">
                        <svg class="h-4 w-4 shrink-0 mt-0.5 text-brand-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m11.25 11.25.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z"/></svg>
                        {{ t('custom_order.measurements_hint', 'Enter any measurements you have. Unsure? Leave them blank — our tailor will call you to confirm.') }}
                    </p>
                </div>
            </div>

            {{-- Notes --}}
            <div class="bg-white border border-brand-100 rounded-2xl shadow-sm overflow-hidden">
                <div class="flex items-center gap-3 px-6 py-4 border-b border-brand-50 bg-brand-50/60">
                    <span class="flex h-8 w-8 items-center justify-center rounded-full bg-brand-800 text-xs font-bold text-white">4</span>
                    <h2 class="font-semibold text-brand-900">{{ t('custom_order.additional_notes', 'Additional notes (optional)') }}</h2>
                </div>
                <div class="px-6 py-5">
                    <textarea name="notes" id="notes" rows="3" placeholder="{{ t('custom_order.notes_placeholder', 'Deadlines, fitting preferences, or questions for the tailor…') }}"
                              class="w-full rounded-xl border border-brand-200 bg-white px-4 py-3 text-sm focus:border-brand-500 focus:ring-2 focus:ring-brand-500/20"></textarea>
                </div>
            </div>

            <div class="flex flex-col sm:flex-row sm:items-center gap-4">
                <button type="submit" class="inline-flex items-center justify-center gap-2 rounded-xl bg-gradient-to-br from-gold-500 to-gold-600 text-brand-950 text-sm font-bold px-8 py-3.5 shadow-lg shadow-gold-500/25 transition hover:from-gold-400 hover:to-gold-500">
                    {{ t('custom_order.submit_request', 'Submit Custom Order Request') }}
                    <svg class="h-4 w-4 rtl-flip" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="m8.25 4.5 7.5 7.5-7.5 7.5"/></svg>
                </button>
                <p class="text-xs text-brand-400 leading-relaxed">{{ t('custom_order.submit_note', 'No payment now — we agree on details, fabric, and price together before anything is made.') }}</p>
            </div>
        </form>

        {{-- Sidebar --}}
        <aside class="space-y-6">
            <div class="rounded-2xl border border-brand-100 bg-white shadow-sm p-6">
                <h2 class="font-semibold text-brand-900 mb-4">{{ t('custom_order.steps_title', 'How it works') }}</h2>
                <ol class="space-y-5">
                    @foreach (['step1', 'step2', 'step3'] as $stepKey)
                        <li class="flex gap-3.5">
                            <span class="mt-0.5 flex h-9 w-9 shrink-0 items-center justify-center rounded-xl bg-teal-50 text-teal-700 text-sm font-bold">{{ $loop->iteration }}</span>
                            <div>
                                <p class="text-sm font-semibold text-brand-900">{{ t('custom_order.' . $stepKey . '_title', 'Step ' . $loop->iteration) }}</p>
                                <p class="mt-1 text-xs text-brand-500 leading-relaxed">{{ t('custom_order.' . $stepKey . '_text', '') }}</p>
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>

            @php $waHelp = \App\Support\WhatsApp::shopLink(t('custom_order.whatsapp_intro', 'Hello! I would like to order a custom (made-to-measure) garment.')); @endphp
            @if ($waHelp)
                <div class="rounded-2xl border border-green-200 bg-green-50 p-6">
                    <div class="flex items-center gap-2.5 mb-2">
                        <svg class="h-5 w-5 text-green-600" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        <h2 class="text-sm font-bold text-green-900">{{ t('custom_order.help_title', 'Need help with measurements?') }}</h2>
                    </div>
                    <p class="text-xs text-green-800 leading-relaxed mb-4">{{ t('custom_order.help_text', 'Chat with our tailoring team on WhatsApp — we can walk you through the numbers before you submit.') }}</p>
                    <a href="{{ $waHelp }}" target="_blank" rel="noopener" class="inline-flex w-full items-center justify-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700">
                        {{ t('contact.start_chat', 'Start Chat') }}
                    </a>
                </div>
            @endif

            <div class="rounded-2xl border border-brand-100 bg-brand-50/60 p-6">
                <h2 class="text-sm font-bold text-brand-900 mb-2">{{ t('custom_order.also_visit_title', 'Browse the collection') }}</h2>
                <p class="text-xs text-brand-600 leading-relaxed mb-4">{{ t('custom_order.also_visit_text', 'Prefer to pick a ready-made garment or a made-to-measure fabric first? Everything is in the shop.') }}</p>
                <a href="{{ route('shop.index') }}" class="text-sm font-semibold text-brand-700 hover:text-brand-900">{{ t('custom_order.also_visit_link', 'Open the shop →') }}</a>
            </div>
        </aside>
    </div>
@endsection
