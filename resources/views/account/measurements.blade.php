@extends('layouts.account')

@section('account-title', 'Measurement Profiles')

@section('robots', 'noindex, nofollow')

@section('account-content')
@php
    $measureLabels = [
        'chest' => t('common.measure_chest', 'Chest'),
        'waist' => t('common.measure_waist', 'Waist'),
        'hips' => t('common.measure_hips', 'Hips'),
        'shoulder' => t('common.measure_shoulder', 'Shoulder Width'),
        'sleeve' => t('common.measure_sleeve', 'Sleeve Length'),
        'shirt_length' => t('common.measure_shirt_length', 'Shirt / Kameez Length'),
        'neck' => t('common.measure_neck', 'Neck'),
        'armhole' => t('common.measure_armhole', 'Armhole'),
        'wrist' => t('common.measure_wrist', 'Wrist'),
    ];
@endphp
<div x-data="{ openForm: {{ $profiles->isEmpty() ? 'true' : 'false' }}, editId: null }">
    <div class="mb-6 flex items-center justify-between">
        <p class="text-sm text-brand-600">{{ t('account.measurements_intro', 'Save your measurements once and apply them instantly during checkout.') }}</p>
        <button type="button" @click="openForm = !openForm; editId = null"
                class="rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800"
                x-text="openForm ? '{{ t('account.close', 'Close') }}' : (editId ? '{{ t('account.close', 'Close') }}' : '{{ t('account.add_new_profile', 'Add New Profile') }}')">
            {{ t('account.add_new_profile', 'Add New Profile') }}
        </button>
    </div>

    @if (session('success'))
        <div class="mb-6 rounded-xl bg-green-50 px-4 py-3 text-sm font-medium text-green-800">{{ session('success') }}</div>
    @endif

    {{-- Create form --}}
    <div x-show="openForm" x-cloak class="mb-8 rounded-2xl border border-brand-100 bg-white p-6 shadow-sm sm:p-8">
        <h3 class="mb-5 text-lg font-semibold text-brand-900">{{ t('account.new_measurement_profile', 'New Measurement Profile') }}</h3>
        <form method="POST" action="{{ route('account.measurements.store') }}">
            @csrf
            <div class="grid gap-4 sm:grid-cols-2">
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('account.profile_label', 'Profile Label') }}</label>
                    <input type="text" name="label" value="{{ old('label') }}" placeholder="{{ t('account.profile_label_placeholder', 'e.g. My usual size') }}"
                           class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                </div>
                @foreach ($fields as $field => $label)
                    <div>
                        <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ $measureLabels[$field] ?? $label }} <span class="font-normal text-brand-400">{{ t('account.inches_suffix', '(inches)') }}</span></label>
                        <input type="number" step="0.1" min="1" max="300" name="{{ $field }}" value="{{ old($field) }}"
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error($field) border-red-400 @enderror">
                        @error($field)<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                @endforeach
                <div class="sm:col-span-2">
                    <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('account.notes', 'Notes') }}</label>
                    <textarea name="notes" rows="2" placeholder="{{ t('account.notes_placeholder', 'Any fitting preferences...') }}"
                              class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">{{ old('notes') }}</textarea>
                </div>
            </div>
            <button type="submit" class="mt-5 rounded-xl bg-teal-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-teal-800">{{ t('account.save_profile', 'Save Profile') }}</button>
        </form>
    </div>

    {{-- Profile cards --}}
    @forelse ($profiles as $profile)
        <div class="mb-6 rounded-2xl border border-brand-100 bg-white p-6 shadow-sm" x-data="{ editing: false }">
            <div class="mb-4 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-semibold text-brand-900">{{ $profile->label }}</h3>
                    <p class="text-xs text-brand-400">{{ t('account.saved_on', 'Saved') }} {{ $profile->created_at->format('M j, Y') }}</p>
                </div>
                <div class="flex items-center gap-2">
                    <button type="button" @click="editing = !editing; if (editing) { openForm = false }"
                            class="rounded-lg border border-brand-200 px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:bg-brand-50"
                            x-text="editing ? '{{ t('account.cancel', 'Cancel') }}' : '{{ t('account.edit', 'Edit') }}'">{{ t('account.edit', 'Edit') }}</button>
                    <form method="POST" action="{{ route('account.measurements.destroy', $profile) }}" onsubmit="return confirm('{{ t('account.confirm_delete_profile', 'Delete this measurement profile?') }}')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50">{{ t('account.delete', 'Delete') }}</button>
                    </form>
                </div>
            </div>

            {{-- View mode --}}
            <div x-show="!editing">
                <dl class="grid grid-cols-2 gap-3 sm:grid-cols-3 lg:grid-cols-5">
                    @foreach ($fields as $field => $label)
                        <div class="rounded-xl bg-brand-50 px-3 py-2">
                            <dt class="text-[11px] font-medium uppercase tracking-wide text-brand-400">{{ $measureLabels[$field] ?? $label }}</dt>
                            <dd class="text-sm font-semibold text-brand-900">{{ $profile->$field !== null ? $profile->$field.'″' : '—' }}</dd>
                        </div>
                    @endforeach
                </dl>
                @if ($profile->notes)
                    <p class="mt-4 rounded-xl bg-brand-50 px-4 py-3 text-sm text-brand-700">{{ $profile->notes }}</p>
                @endif
            </div>

            {{-- Edit mode --}}
            <div x-show="editing" x-cloak>
                <form method="POST" action="{{ route('account.measurements.update', $profile) }}">
                    @csrf
                    @method('PUT')
                    <div class="grid gap-4 sm:grid-cols-2 lg:grid-cols-3">
                        <div class="sm:col-span-2 lg:col-span-3">
                            <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('account.profile_label', 'Profile Label') }}</label>
                            <input type="text" name="label" value="{{ $profile->label }}"
                                   class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                        </div>
                        @foreach ($fields as $field => $label)
                            <div>
                                <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ $measureLabels[$field] ?? $label }} <span class="font-normal text-brand-400">{{ t('account.inches_suffix', '(inches)') }}</span></label>
                                <input type="number" step="0.1" min="1" max="300" name="{{ $field }}" value="{{ $profile->$field }}"
                                       class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                            </div>
                        @endforeach
                        <div class="sm:col-span-2 lg:col-span-3">
                            <label class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('account.notes', 'Notes') }}</label>
                            <textarea name="notes" rows="2" class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">{{ $profile->notes }}</textarea>
                        </div>
                    </div>
                    <button type="submit" class="mt-5 rounded-xl bg-teal-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-teal-800">{{ t('account.update_profile', 'Update Profile') }}</button>
                </form>
            </div>
        </div>
    @empty
        <div x-show="!openForm" class="rounded-2xl border border-dashed border-brand-200 bg-white/60 p-10 text-center">
            <p class="text-sm text-brand-500">{{ t('account.no_profiles', 'No measurement profiles yet. Add one so checkout is faster next time.') }}</p>
        </div>
    @endforelse
</div>
@endsection
