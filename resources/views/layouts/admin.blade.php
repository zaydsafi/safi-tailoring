<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ is_rtl() ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', t('admin.common.admin_panel', 'Admin Panel')) · {{ $shopSettings['shop_name'] ?? 'Safi Tailoring Shop' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-brand-50 font-sans text-brand-900 antialiased">
<div x-data="{ sidebarOpen: false }">
    {{-- Mobile sidebar backdrop --}}
    <div x-show="sidebarOpen" x-cloak class="fixed inset-0 z-30 bg-brand-950/50 lg:hidden" @click="sidebarOpen = false"></div>

    {{-- Sidebar --}}
    <aside class="fixed inset-y-0 start-0 z-40 w-64 transform bg-brand-900 text-white transition-transform duration-200 lg:translate-x-0!"
           :class="sidebarOpen ? 'translate-x-0' : 'rtl:translate-x-full -translate-x-full'">
        <div class="flex h-16 items-center gap-2 border-b border-white/10 px-6">
            <svg class="h-7 w-7 text-gold-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>
            </svg>
            <div>
                <p class="text-sm font-bold leading-tight">{{ $shopSettings['shop_name'] ?? 'Safi Tailoring' }}</p>
                <p class="text-[11px] text-brand-300">{{ t('admin.common.admin_panel', 'Admin Panel') }}</p>
            </div>
        </div>

        @php
            $nav = [
                ['route' => 'admin.dashboard', 'label' => t('admin.nav.dashboard', 'Dashboard'), 'icon' => 'M3.75 6A2.25 2.25 0 016 3.75h2.25A2.25 2.25 0 0110.5 6v2.25a2.25 2.25 0 01-2.25 2.25H6a2.25 2.25 0 01-2.25-2.25V6zM3.75 15.75A2.25 2.25 0 016 13.5h2.25a2.25 2.25 0 012.25 2.25V18a2.25 2.25 0 01-2.25 2.25H6A2.25 2.25 0 013.75 18v-2.25zM13.5 6a2.25 2.25 0 012.25-2.25H18A2.25 2.25 0 0120.25 6v2.25A2.25 2.25 0 0118 10.5h-2.25a2.25 2.25 0 01-2.25-2.25V6zM13.5 15.75a2.25 2.25 0 012.25-2.25H18a2.25 2.25 0 012.25 2.25V18A2.25 2.25 0 0118 20.25h-2.25A2.25 2.25 0 0113.5 18v-2.25z'],
                ['route' => 'admin.orders.index', 'label' => t('admin.nav.orders', 'Orders'), 'icon' => 'M9 12h6m-6 4h6M9 8h6M5.25 3h13.5A1.75 1.75 0 0120.5 4.75v14.5A1.75 1.75 0 0118.75 21H5.25a1.75 1.75 0 01-1.75-1.75V4.75A1.75 1.75 0 015.25 3z'],
                ['route' => 'admin.products.index', 'label' => t('admin.nav.products', 'Products'), 'icon' => 'M20.25 7.5l-.625 10.632a2.25 2.25 0 01-2.247 2.118H6.622a2.25 2.25 0 01-2.247-2.118L3.75 7.5M10 11.25h4M3.375 7.5h17.25c.621 0 1.125-.504 1.125-1.125v-1.5c0-.621-.504-1.125-1.125-1.125H3.375c-.621 0-1.125.504-1.125 1.125v1.5c0 .621.504 1.125 1.125 1.125z'],
                ['route' => 'admin.categories.index', 'label' => t('admin.nav.categories', 'Categories'), 'icon' => 'M8.25 6.75h12M8.25 12h12m-12 5.25h12M3.75 6.75h.007v.008H3.75V6.75zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zM3.75 12h.007v.008H3.75V12zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0zm-.375 5.25h.007v.008H3.75v-.008zm.375 0a.375.375 0 11-.75 0 .375.375 0 01.75 0z'],
                ['route' => 'admin.users.index', 'label' => t('admin.nav.users', 'Users'), 'match' => ['admin.users.*', 'admin.customers.*'], 'icon' => 'M15 19.128a9.38 9.38 0 002.625.372 9.337 9.337 0 004.121-.952 4.125 4.125 0 00-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 018.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0111.964-3.07M12 6.375a3.375 3.375 0 11-6.75 0 3.375 3.375 0 016.75 0zm8.25 2.25a2.625 2.625 0 11-5.25 0 2.625 2.625 0 015.25 0z'],
                ['route' => 'admin.messages.index', 'label' => t('admin.nav.messages', 'Messages'), 'icon' => 'M21.75 6.75v10.5a2.25 2.25 0 01-2.25 2.25h-15a2.25 2.25 0 01-2.25-2.25V6.75m19.5 0A2.25 2.25 0 0019.5 4.5h-15a2.25 2.25 0 00-2.25 2.25m19.5 0v.243a2.25 2.25 0 01-1.07 1.916l-7.5 4.615a2.25 2.25 0 01-2.36 0L3.32 8.91a2.25 2.25 0 01-1.07-1.916V6.75'],
                ['route' => 'admin.translations.index', 'label' => t('admin.nav.translations', 'Translations'), 'match' => ['admin.translations.*'], 'icon' => 'M10.5 21l5.25-11.25L21 21m-9-3h7.5M3 5.621a48.474 48.474 0 0 1 6-.371m0 0c1.12 0 2.233.038 3.334.114M9 5.25V3m3.334 2.364C11.176 10.658 7.69 15.08 3 17.502m9.334-12.138c.896.061 1.785.147 2.666.257m-4.589 8.495a18.023 18.023 0 0 1-3.827-5.802'],
                ['route' => 'admin.settings.edit', 'label' => t('admin.nav.settings', 'Settings'), 'icon' => 'M9.594 3.94c.09-.542.56-.94 1.11-.94h2.593c.55 0 1.02.398 1.11.94l.213 1.281c.063.374.313.686.645.87.074.04.147.083.22.127.324.196.72.257 1.075.124l1.217-.456a1.125 1.125 0 011.37.49l1.296 2.247a1.125 1.125 0 01-.26 1.431l-1.003.827c-.293.24-.438.613-.431.992a6.759 6.759 0 010 .255c-.007.378.138.75.43.99l1.005.828c.424.35.534.954.26 1.43l-1.298 2.247a1.125 1.125 0 01-1.369.491l-1.217-.456c-.355-.133-.75-.072-1.076.124a6.57 6.57 0 01-.22.128c-.331.183-.581.495-.644.869l-.213 1.28c-.09.543-.56.941-1.11.941h-2.594c-.55 0-1.02-.398-1.11-.94l-.213-1.281c-.062-.374-.312-.686-.644-.87a6.52 6.52 0 01-.22-.127c-.325-.196-.72-.257-1.076-.124l-1.217.456a1.125 1.125 0 01-1.369-.49l-1.297-2.247a1.125 1.125 0 01.26-1.431l1.004-.827c.292-.24.437-.613.43-.992a6.932 6.932 0 010-.255c.007-.378-.138-.75-.43-.99l-1.004-.828a1.125 1.125 0 01-.26-1.43l1.297-2.247a1.125 1.125 0 011.37-.491l1.216.456c.356.133.751.072 1.076-.124.072-.044.146-.087.22-.128.332-.183.582-.495.644-.869l.214-1.281zM15 12a3 3 0 11-6 0 3 3 0 016 0z'],
            ];
        @endphp

        <nav class="space-y-1 px-3 py-4">
            @foreach ($nav as $item)
                <a href="{{ route($item['route']) }}"
                   class="flex items-center gap-3 rounded-xl px-3 py-2.5 text-sm font-medium transition {{ request()->routeIs($item['match'] ?? str_replace('.index', '.*', $item['route'])) || request()->routeIs($item['route']) ? 'bg-teal-700 text-white' : 'text-brand-200 hover:bg-white/10 hover:text-white' }}">
                    <svg class="h-5 w-5 shrink-0" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" d="{{ $item['icon'] }}"/>
                    </svg>
                    {{ $item['label'] }}
                </a>
            @endforeach
        </nav>

        <div class="absolute inset-x-0 bottom-0 border-t border-white/10 p-4">
            <nav class="mb-3 flex items-center gap-1 rounded-full bg-white/5 p-1 lg:hidden" aria-label="{{ t('admin.common.language', 'Language') }}">
                @foreach (\App\Support\Locales::SUPPORTED as $code => $info)
                    <a href="{{ route('admin.lang', $code) }}"
                       class="flex-1 rounded-full px-2 py-1.5 text-center text-xs font-semibold transition {{ $code === app()->getLocale() ? 'bg-teal-700 text-white' : 'text-brand-200 hover:bg-white/10 hover:text-white' }}"
                       @if ($code === app()->getLocale()) aria-current="true" @endif>{{ $info['native'] }}</a>
                @endforeach
            </nav>
            <a href="{{ route('home') }}" target="_blank" class="mb-2 flex items-center gap-2 rounded-xl px-3 py-2 text-sm text-brand-200 transition hover:bg-white/10 hover:text-white">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13.5 6H5.25A2.25 2.25 0 003 8.25v10.5A2.25 2.25 0 005.25 21h10.5A2.25 2.25 0 0018 18.75V10.5m-4.5-6L21 3m0 0l-3.75 0M21 3v3.75"/></svg>
                {{ t('admin.common.view_storefront', 'View Storefront') }}
            </a>
            <form method="POST" action="{{ route('admin.logout') }}">
                @csrf
                <button type="submit" class="flex w-full items-center gap-2 rounded-xl px-3 py-2 text-sm text-brand-200 transition hover:bg-white/10 hover:text-white">
                    <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 9V5.25A2.25 2.25 0 0013.5 3h-6a2.25 2.25 0 00-2.25 2.25v13.5A2.25 2.25 0 007.5 21h6a2.25 2.25 0 002.25-2.25V15m3 0l3-3m0 0l-3-3m3 3H9"/></svg>
                    {{ t('admin.common.sign_out', 'Sign Out') }}
                </button>
            </form>
        </div>
    </aside>

    {{-- Main --}}
    <div class="lg:ps-64">
        <header class="sticky top-0 z-20 flex h-16 items-center gap-4 border-b border-brand-100 bg-white/90 px-4 backdrop-blur sm:px-6">
            <button type="button" @click="sidebarOpen = true" class="rounded-lg p-2 text-brand-600 hover:bg-brand-50 lg:hidden">
                <svg class="h-6 w-6" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
            </button>
            <h1 class="text-lg font-bold text-brand-900">@yield('title', t('admin.nav.dashboard', 'Dashboard'))</h1>
            <div class="ms-auto flex items-center gap-3">
                <nav class="hidden sm:flex items-center rounded-full border border-brand-100 bg-brand-50 p-0.5" aria-label="{{ t('admin.common.language', 'Language') }}">
                    @foreach (\App\Support\Locales::SUPPORTED as $code => $info)
                        <a href="{{ route('admin.lang', $code) }}"
                           class="rounded-full px-2.5 py-1 text-xs font-semibold transition {{ $code === app()->getLocale() ? 'bg-brand-800 text-white shadow-sm' : 'text-brand-500 hover:text-brand-800' }}"
                           @if ($code === app()->getLocale()) aria-current="true" @endif>{{ $info['native'] }}</a>
                    @endforeach
                </nav>
                <span class="hidden text-sm text-brand-500 sm:block">{{ auth()->user()->name }}</span>
                <span class="flex h-9 w-9 items-center justify-center rounded-full bg-teal-700 text-sm font-bold text-white">{{ strtoupper(substr(auth()->user()->name, 0, 1)) }}</span>
            </div>
        </header>

        <main class="p-4 sm:p-6">
            @if (session('success'))
                <div class="mb-6 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-800">{{ session('success') }}</div>
            @endif
            @if (session('error'))
                <div class="mb-6 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700">{{ session('error') }}</div>
            @endif

            @yield('content')
        </main>
    </div>
</div>
</body>
</html>
