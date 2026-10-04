<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Admin Login · {{ $shopSettings['shop_name'] ?? 'Safi Tailoring Shop' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="flex min-h-screen items-center justify-center bg-brand-900 px-4 font-sans antialiased">
    <div class="w-full max-w-md">
        <div class="mb-8 text-center">
            <div class="mx-auto mb-4 flex h-14 w-14 items-center justify-center rounded-2xl bg-gold-400/20">
                <svg class="h-8 w-8 text-gold-400" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9.53 16.122a3 3 0 00-5.78 1.128 2.25 2.25 0 01-2.4 2.245 4.5 4.5 0 008.4-2.245c0-.399-.078-.78-.22-1.128zm0 0a15.998 15.998 0 003.388-1.62m-5.043-.025a15.994 15.994 0 011.622-3.395m3.42 3.42a15.995 15.995 0 004.764-4.648l3.876-5.814a1.151 1.151 0 00-1.597-1.597L14.146 6.32a15.996 15.996 0 00-4.649 4.763m3.42 3.42a6.776 6.776 0 00-3.42-3.42"/>
                </svg>
            </div>
            <h1 class="font-display text-2xl font-bold text-white">{{ $shopSettings['shop_name'] ?? 'Safi Tailoring Shop' }}</h1>
            <p class="mt-1 text-sm text-brand-300">Admin Panel</p>
        </div>

        <div class="rounded-2xl bg-white p-8 shadow-2xl">
            @if (session('success'))
                <div class="mb-5 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-800">{{ session('success') }}</div>
            @endif

            <form method="POST" action="{{ route('admin.login.store') }}" class="space-y-5">
                @csrf
                <div>
                    <label for="email" class="mb-1.5 block text-sm font-medium text-brand-900">Email Address</label>
                    <input type="email" id="email" name="email" value="{{ old('email') }}" required autofocus
                           class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('email') border-red-400 @enderror">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="password" class="mb-1.5 block text-sm font-medium text-brand-900">Password</label>
                    <input type="password" id="password" name="password" required
                           class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('password') border-red-400 @enderror">
                    @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <label class="flex items-center gap-2 text-sm text-brand-600">
                    <input type="checkbox" name="remember" value="1" class="h-4 w-4 rounded border-brand-300 text-teal-700 focus:ring-teal-600/30">
                    Remember me
                </label>
                <button type="submit" class="w-full rounded-xl bg-teal-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-teal-800">Sign In</button>
            </form>
        </div>

        <p class="mt-6 text-center text-xs text-brand-400"><a href="{{ route('home') }}" class="underline hover:text-white">Back to storefront</a></p>
    </div>
</body>
</html>
