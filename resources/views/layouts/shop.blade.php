@php
    $currentLocale = app()->getLocale();
    $locales = \App\Support\Locales::SUPPORTED;
    $localeInfo = $locales[$currentLocale] ?? $locales['en'];

    $metaTitle = trim($__env->yieldContent('title')) ?: $shopSettings['shop_name'];
    $metaDescription = trim($__env->yieldContent('meta_description')) ?: ($shopSettings['shop_tagline'] ?: $shopSettings['shop_name']);
    $ogImageRaw = trim($__env->yieldContent('og_image'));
    $metaImage = $ogImageRaw === ''
        ? null
        : (\Illuminate\Support\Str::startsWith($ogImageRaw, ['http://', 'https://']) ? $ogImageRaw : url($ogImageRaw));

    $canonical = url()->current();
    if (request()->query('page')) {
        $canonical .= '?page=' . request()->query('page');
    }

    $basePath = implode('/', array_slice(request()->segments(), 1));
    $alternateUrl = fn (string $code) => url('/' . $code . ($basePath !== '' ? '/' . $basePath : ''));

    $organizationSchema = array_filter([
        '@context' => 'https://schema.org',
        '@type' => 'Organization',
        'name' => $shopSettings['shop_name'],
        'url' => url('/' . $currentLocale),
        'telephone' => $shopSettings['shop_phone'],
        'email' => $shopSettings['shop_email'],
        'address' => $shopSettings['shop_address'] ? ['@type' => 'PostalAddress', 'streetAddress' => $shopSettings['shop_address']] : null,
        'sameAs' => array_values(array_filter([$shopSettings['facebook_url'], $shopSettings['instagram_url']])),
    ], fn ($value) => $value !== null && $value !== '');

    $websiteSchema = [
        '@context' => 'https://schema.org',
        '@type' => 'WebSite',
        'name' => $shopSettings['shop_name'],
        'url' => url('/' . $currentLocale),
        'inLanguage' => $currentLocale,
    ];
@endphp
<!DOCTYPE html>
<html lang="{{ $currentLocale }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <meta name="robots" content="@yield('robots', 'index, follow')">
    <meta name="theme-color" content="#0f3b39">
    <link rel="canonical" href="{{ $canonical }}">
    @foreach ($locales as $code => $info)
        <link rel="alternate" hreflang="{{ $code }}" href="{{ $alternateUrl($code) }}">
    @endforeach
    <link rel="alternate" hreflang="x-default" href="{{ $alternateUrl('en') }}">

    <meta property="og:site_name" content="{{ $shopSettings['shop_name'] }}">
    <meta property="og:type" content="@yield('og_type', 'website')">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ $canonical }}">
    <meta property="og:locale" content="{{ $localeInfo['og'] }}">
    @foreach ($locales as $code => $info)
        @if ($code !== $currentLocale)
            <meta property="og:locale:alternate" content="{{ $info['og'] }}">
        @endif
    @endforeach
    @if ($metaImage)
        <meta property="og:image" content="{{ $metaImage }}">
    @endif
    <meta name="twitter:card" content="{{ $metaImage ? 'summary_large_image' : 'summary' }}">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    @if ($metaImage)
        <meta name="twitter:image" content="{{ $metaImage }}">
    @endif

    <script type="application/ld+json">{!! json_encode($organizationSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    <script type="application/ld+json">{!! json_encode($websiteSchema, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE) !!}</script>
    @stack('seo')

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans bg-white text-gray-800 antialiased" x-data="{ mobileOpen: false }">

    @if($shopSettings['announcement'])
        <div class="bg-brand-900 text-white text-center text-sm py-2 px-4">
            {{ t('common.announcement', $shopSettings['announcement']) }}
        </div>
    @endif

    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur border-b border-gray-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 gap-3 sm:gap-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2 shrink-0">
                    <svg class="w-8 h-8 text-brand-700" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 0 0-5.78 1.128 2.25 2.25 0 0 1-2.4 2.245 4.5 4.5 0 0 0 8.4-2.245c0-.399-.078-.78-.22-1.128Zm0 0a15.998 15.998 0 0 0 3.388-1.62m-5.043-.025a15.994 15.994 0 0 1 1.622-3.395m3.42 3.42a15.995 15.995 0 0 0 4.764-1.488m3.42 3.42a15.995 15.995 0 0 1 1.622 3.395m-2.25 4.764c.101.324.151.66.151 1.001 0 1.036-.313 2-.85 2.8m-5.186-3.023a15.994 15.994 0 0 1-3.388 1.62m5.32-1.168a15.998 15.998 0 0 0-3.42-3.42m1.303 4.764c-.04.322-.06.65-.06.982 0 1.036.313 2 .85 2.8M6.53 9.53a15.994 15.994 0 0 0-1.622 3.395m2.143-4.75A15.995 15.995 0 0 1 12 7.5c.352 0 .697.03 1.032.086"/>
                    </svg>
                    <span class="font-display font-bold text-lg sm:text-xl text-brand-900 leading-tight">
                        {{ $shopSettings['shop_name'] }}
                    </span>
                </a>

                <nav class="hidden md:flex items-center gap-4 lg:gap-6 text-sm font-medium text-gray-700">
                    <a href="{{ route('home') }}" class="hover:text-brand-700 {{ request()->routeIs('home') ? 'text-brand-700' : '' }}">{{ t('nav.home', 'Home') }}</a>
                    <a href="{{ route('shop.index') }}" class="hover:text-brand-700 {{ request()->routeIs('shop.*') ? 'text-brand-700' : '' }}">{{ t('nav.shop', 'Shop') }}</a>
                    <a href="{{ route('about') }}" class="hover:text-brand-700 {{ request()->routeIs('about') ? 'text-brand-700' : '' }}">{{ t('nav.about', 'About') }}</a>
                    <a href="{{ route('contact') }}" class="hover:text-brand-700 {{ request()->routeIs('contact') ? 'text-brand-700' : '' }}">{{ t('nav.contact', 'Contact') }}</a>
                    <a href="{{ route('orders.track') }}" class="hover:text-brand-700 {{ request()->routeIs('orders.track') ? 'text-brand-700' : '' }}">{{ t('nav.track_order', 'Track Order') }}</a>
                </nav>

                <div class="flex items-center gap-2 sm:gap-4">
                    <nav class="hidden sm:flex items-center rounded-full border border-gray-200 bg-gray-50 p-0.5" aria-label="{{ t('common.language', 'Language') }}">
                        @foreach ($locales as $code => $info)
                            <a href="{{ switch_locale_url($code) }}"
                               class="rounded-full px-2.5 py-1 text-xs font-semibold transition {{ $code === $currentLocale ? 'bg-brand-800 text-white shadow-sm' : 'text-gray-600 hover:text-brand-800' }}"
                               @if ($code === $currentLocale) aria-current="true" @endif>{{ $info['native'] }}</a>
                        @endforeach
                    </nav>

                    <a href="{{ route('cart.index') }}" class="relative p-2 text-gray-700 hover:text-brand-700" aria-label="{{ t('nav.cart', 'Cart') }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 10.5V6a3.75 3.75 0 1 0-7.5 0v4.5m11.356-1.993 1.263 12c.07.665-.45 1.243-1.119 1.243H4.25a1.125 1.125 0 0 1-1.12-1.243l1.264-12A1.125 1.125 0 0 1 5.513 7.5h12.974c.576 0 1.059.435 1.119 1.007Z"/>
                        </svg>
                        @if($cartCount > 0)
                            <span class="absolute -top-1 -right-1 bg-gold-500 text-white text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center">{{ $cartCount }}</span>
                        @endif
                    </a>

                    @auth
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" class="flex items-center gap-1 text-sm font-medium text-gray-700 hover:text-brand-700 p-2">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17.982 18.725A7.488 7.488 0 0 0 12 15.75a7.488 7.488 0 0 0-5.982 2.975m11.963 0a9 9 0 1 0-11.963 0m11.963 0A8.966 8.966 0 0 1 12 21a8.966 8.966 0 0 1-5.982-2.275M15 9.75a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z"/>
                                </svg>
                                <span class="hidden lg:inline">{{ auth()->user()->name }}</span>
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="m19.5 8.25-7.5 7.5-7.5-7.5"/></svg>
                            </button>
                            <div x-cloak x-show="open" @click.outside="open = false" class="absolute end-0 mt-2 w-48 bg-white rounded-lg shadow-lg border border-gray-200 py-1 text-sm">
                                <a href="{{ route('account.orders') }}" class="block px-4 py-2 hover:bg-gray-50">{{ t('nav.my_orders', 'My Orders') }}</a>
                                <a href="{{ route('account.measurements') }}" class="block px-4 py-2 hover:bg-gray-50">{{ t('nav.my_measurements', 'My Measurements') }}</a>
                                <a href="{{ route('account.profile') }}" class="block px-4 py-2 hover:bg-gray-50">{{ t('nav.profile', 'Profile') }}</a>
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full text-start px-4 py-2 hover:bg-gray-50 text-red-600">{{ t('nav.sign_out', 'Sign Out') }}</button>
                                </form>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="hidden sm:inline text-sm font-medium text-gray-700 hover:text-brand-700">{{ t('nav.sign_in', 'Sign In') }}</a>
                        <a href="{{ route('register') }}" class="text-sm font-medium bg-brand-800 text-white px-4 py-2 rounded-lg hover:bg-brand-700">{{ t('nav.register', 'Register') }}</a>
                    @endauth

                    <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 text-gray-700" aria-label="{{ t('nav.menu', 'Menu') }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path stroke-linecap="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/>
                        </svg>
                    </button>
                </div>
            </div>
        </div>

        <div x-cloak x-show="mobileOpen" class="md:hidden border-t border-gray-200 bg-white">
            <nav class="px-4 py-3 space-y-1 text-sm font-medium text-gray-700">
                <a href="{{ route('home') }}" class="block py-2 hover:text-brand-700">{{ t('nav.home', 'Home') }}</a>
                <a href="{{ route('shop.index') }}" class="block py-2 hover:text-brand-700">{{ t('nav.shop', 'Shop') }}</a>
                <a href="{{ route('about') }}" class="block py-2 hover:text-brand-700">{{ t('nav.about', 'About') }}</a>
                <a href="{{ route('contact') }}" class="block py-2 hover:text-brand-700">{{ t('nav.contact', 'Contact') }}</a>
                <a href="{{ route('orders.track') }}" class="block py-2 hover:text-brand-700">{{ t('nav.track_order', 'Track Order') }}</a>
                @guest
                    <a href="{{ route('login') }}" class="block py-2 hover:text-brand-700">{{ t('nav.sign_in', 'Sign In') }}</a>
                @endguest
                <div class="mt-2 flex items-center gap-2 border-t border-gray-100 pt-3">
                    <span class="text-xs text-gray-400">{{ t('common.language', 'Language') }}</span>
                    @foreach ($locales as $code => $info)
                        <a href="{{ switch_locale_url($code) }}"
                           class="rounded-full px-3 py-1 text-xs font-semibold {{ $code === $currentLocale ? 'bg-brand-800 text-white' : 'border border-gray-200 text-gray-600' }}"
                           @if ($code === $currentLocale) aria-current="true" @endif>{{ $info['native'] }}</a>
                    @endforeach
                </div>
            </nav>
        </div>
    </header>

    @if(session('success') || session('error'))
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4 space-y-2">
            @if(session('success'))
                <div class="bg-green-50 border border-green-200 text-green-800 rounded-lg px-4 py-3 text-sm">{{ session('success') }}</div>
            @endif
            @if(session('error'))
                <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">{{ session('error') }}</div>
            @endif
        </div>
    @endif

    @if($errors->any())
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-4">
            <div class="bg-red-50 border border-red-200 text-red-800 rounded-lg px-4 py-3 text-sm">
                <p class="font-semibold mb-1">{{ t('common.please_fix', 'Please fix the following:') }}</p>
                <ul class="list-disc list-inside space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
    @endif

    <main>
        @yield('content')
    </main>

    <footer class="bg-brand-950 text-gray-300 mt-16">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 grid gap-8 md:grid-cols-4">
            <div>
                <h3 class="font-display text-lg font-bold text-white mb-3">{{ $shopSettings['shop_name'] }}</h3>
                <p class="text-sm text-gray-400">{{ t('footer.tagline', $shopSettings['shop_tagline']) }}</p>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3 text-sm uppercase tracking-wide">{{ t('footer.shop', 'Shop') }}</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('shop.index') }}" class="hover:text-white">{{ t('footer.all_products', 'All Products') }}</a></li>
                    <li><a href="{{ route('shop.index', ['type' => 'custom']) }}" class="hover:text-white">{{ t('footer.custom_tailoring', 'Custom Tailoring') }}</a></li>
                    <li><a href="{{ route('shop.index', ['type' => 'service']) }}" class="hover:text-white">{{ t('footer.alterations', 'Alteration Services') }}</a></li>
                    <li><a href="{{ route('orders.track') }}" class="hover:text-white">{{ t('footer.track_order', 'Track Your Order') }}</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3 text-sm uppercase tracking-wide">{{ t('footer.help', 'Help') }}</h4>
                <ul class="space-y-2 text-sm">
                    <li><a href="{{ route('about') }}" class="hover:text-white">{{ t('footer.about_us', 'About Us') }}</a></li>
                    <li><a href="{{ route('contact') }}" class="hover:text-white">{{ t('footer.contact_us', 'Contact Us') }}</a></li>
                    <li><a href="{{ route('login') }}" class="hover:text-white">{{ t('nav.sign_in', 'Sign In') }}</a></li>
                    <li><a href="{{ route('register') }}" class="hover:text-white">{{ t('nav.create_account', 'Create Account') }}</a></li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-3 text-sm uppercase tracking-wide">{{ t('footer.contact', 'Contact') }}</h4>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li>{{ $shopSettings['shop_address'] }}</li>
                    <li>{{ $shopSettings['shop_phone'] }}</li>
                    <li>{{ $shopSettings['shop_email'] }}</li>
                </ul>
                @if($shopSettings['facebook_url'] || $shopSettings['instagram_url'] || $shopSettings['whatsapp_number'])
                    <div class="flex gap-3 mt-4">
                        @if($shopSettings['facebook_url'])
                            <a href="{{ $shopSettings['facebook_url'] }}" target="_blank" rel="noopener" class="hover:text-white">Facebook</a>
                        @endif
                        @if($shopSettings['instagram_url'])
                            <a href="{{ $shopSettings['instagram_url'] }}" target="_blank" rel="noopener" class="hover:text-white">Instagram</a>
                        @endif
                        @if($shopSettings['whatsapp_number'])
                            <a href="{{ \App\Support\WhatsApp::link($shopSettings['whatsapp_number']) }}" target="_blank" rel="noopener" class="hover:text-white">WhatsApp</a>
                        @endif
                    </div>
                @endif
            </div>
        </div>
        <div class="border-t border-white/10 py-4 text-center text-xs text-gray-500">
            &copy; {{ date('Y') }} {{ $shopSettings['shop_name'] }}. {{ t('footer.rights', 'All rights reserved.') }}
        </div>
    </footer>

</body>
</html>
