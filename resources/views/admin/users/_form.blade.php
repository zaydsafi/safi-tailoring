@php
    $user = $user ?? null;
    $isSelf = $user && $user->is(auth()->user());
@endphp

<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <label for="name" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.users.full_name', 'Full Name') }} <span class="text-red-500">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', $user->name ?? '') }}" required
               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('name') border-red-400 @enderror">
        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="email" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.common.email', 'Email') }} <span class="text-red-500">*</span></label>
        <input type="email" id="email" name="email" value="{{ old('email', $user->email ?? '') }}" required
               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('email') border-red-400 @enderror">
        @error('email')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="phone" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.users.phone', 'Phone / WhatsApp') }} <span class="text-xs font-normal text-brand-400">{{ t('admin.users.phone_hint', '(used for customer login)') }}</span></label>
        <input type="text" id="phone" name="phone" value="{{ old('phone', $user->phone ?? '') }}" placeholder="{{ t('admin.users.phone_placeholder', 'e.g. 0700 111 111') }}"
               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('phone') border-red-400 @enderror">
        @error('phone')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="role" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.users.role', 'Role') }} <span class="text-red-500">*</span></label>
        @if ($isSelf)
            <input type="hidden" name="role" value="{{ $user->role }}">
            <select id="role" disabled class="w-full cursor-not-allowed rounded-xl border border-brand-200 bg-brand-50 px-4 py-2.5 text-sm text-brand-500">
                <option>{{ t('admin.users.role_admin', 'Admin') }}</option>
            </select>
            <p class="mt-1 text-xs text-brand-400">{{ t('admin.users.cannot_change_own_role', 'You cannot change your own role.') }}</p>
        @else
            <select id="role" name="role"
                    class="w-full rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('role') border-red-400 @enderror">
                <option value="customer" @selected(old('role', $user->role ?? 'customer') === 'customer')>{{ t('admin.users.role_customer', 'Customer') }}</option>
                <option value="admin" @selected(old('role', $user->role ?? 'customer') === 'admin')>{{ t('admin.users.role_admin', 'Admin') }}</option>
            </select>
        @endif
        @error('role')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="address" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.common.address', 'Address') }}</label>
        <input type="text" id="address" name="address" value="{{ old('address', $user->address ?? '') }}"
               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('address') border-red-400 @enderror">
        @error('address')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="city" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.users.city', 'City') }}</label>
        <input type="text" id="city" name="city" value="{{ old('city', $user->city ?? '') }}"
               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('city') border-red-400 @enderror">
        @error('city')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="password" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.users.password', 'Password') }} @if (! $user)<span class="text-red-500">*</span>@endif</label>
        <input type="password" id="password" name="password" autocomplete="new-password" @if (! $user) required @endif
               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('password') border-red-400 @enderror">
        @if ($user)
            <p class="mt-1 text-xs text-brand-400">{{ t('admin.users.password_hint', 'Leave blank to keep the current password.') }}</p>
        @endif
        @error('password')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>

    <div>
        <label for="password_confirmation" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.users.confirm_password', 'Confirm Password') }} @if (! $user)<span class="text-red-500">*</span>@endif</label>
        <input type="password" id="password_confirmation" name="password_confirmation" autocomplete="new-password" @if (! $user) required @endif
               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
    </div>
</div>
