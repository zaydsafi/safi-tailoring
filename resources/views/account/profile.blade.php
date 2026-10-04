@extends('layouts.account')

@section('account-title', 'My Profile')

@section('robots', 'noindex, nofollow')

@section('account-content')
    <div class="max-w-2xl">
        <form method="POST" action="{{ route('account.profile.update') }}" class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm sm:p-8">
            @csrf
            @method('PUT')

            @if (session('status') === 'profile-updated')
                <div class="mb-6 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-800">{{ t('account.profile_updated', 'Profile updated successfully.') }}</div>
            @endif

            <div class="grid gap-5 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label for="name" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('account.full_name', 'Full Name') }}</label>
                    <input type="text" id="name" name="name" value="{{ old('name', auth()->user()->name) }}" required
                           class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('name') border-red-400 @enderror">
                    @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="email" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('account.email_address', 'Email Address') }}</label>
                    <input type="email" id="email" name="email" value="{{ old('email', auth()->user()->email) }}" required
                           class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('email') border-red-400 @enderror">
                    @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="phone" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('account.phone', 'Phone') }}</label>
                    <input type="text" id="phone" name="phone" value="{{ old('phone', auth()->user()->phone) }}"
                           class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('phone') border-red-400 @enderror">
                    @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label for="city" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('account.city', 'City') }}</label>
                    <input type="text" id="city" name="city" value="{{ old('city', auth()->user()->city) }}"
                           class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('city') border-red-400 @enderror">
                    @error('city')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="sm:col-span-2">
                    <label for="address" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('account.address', 'Address') }}</label>
                    <textarea id="address" name="address" rows="2"
                              class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('address') border-red-400 @enderror">{{ old('address', auth()->user()->address) }}</textarea>
                    @error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="rounded-xl bg-teal-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-teal-800">{{ t('account.save_profile', 'Save Profile') }}</button>
            </div>
        </form>

        <form method="POST" action="{{ route('account.password.update') }}" class="mt-8 rounded-2xl border border-brand-100 bg-white p-6 shadow-sm sm:p-8">
            @csrf
            @method('PUT')

            <h3 class="text-lg font-semibold text-brand-900">{{ t('account.change_password', 'Change Password') }}</h3>
            @if (session('status') === 'password-updated')
                <div class="mt-4 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-800">{{ t('account.password_changed', 'Password changed successfully.') }}</div>
            @endif

            <div class="mt-5 grid gap-5">
                <div>
                    <label for="current_password" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('account.current_password', 'Current Password') }}</label>
                    <input type="password" id="current_password" name="current_password" required
                           class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('current_password', 'updatePassword') border-red-400 @enderror">
                    @error('current_password', 'updatePassword')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                </div>
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="password" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('account.new_password', 'New Password') }}</label>
                        <input type="password" id="password" name="password" required
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('password', 'updatePassword') border-red-400 @enderror">
                        @error('password', 'updatePassword')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('account.confirm_new_password', 'Confirm New Password') }}</label>
                        <input type="password" id="password_confirmation" name="password_confirmation" required
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                    </div>
                </div>
            </div>

            <div class="mt-6">
                <button type="submit" class="rounded-xl bg-brand-900 px-6 py-3 text-sm font-semibold text-white transition hover:bg-brand-800">{{ t('account.change_password', 'Change Password') }}</button>
            </div>
        </form>
    </div>
@endsection
