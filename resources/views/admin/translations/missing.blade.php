@extends('layouts.admin')

@section('title', t('admin.translations.missing_title', 'Missing Translations'))

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div>
            <h1 class="text-2xl font-bold text-brand-900">{{ t('admin.translations.missing_title', 'Missing Translations') }}</h1>
            <p class="mt-1 text-sm text-brand-500">{{ t('admin.translations.missing_subtitle', 'Keys that are still untranslated show up here so nothing is missed.') }}</p>
        </div>
        <a href="{{ route('admin.translations.index') }}" class="rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">{{ t('admin.translations.back_to_all', '← All translations') }}</a>
    </div>

    {{-- Locale tabs --}}
    <div class="mb-4 flex flex-wrap gap-2">
        <a href="{{ route('admin.translations.missing') }}"
           class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ $locale === 'all' ? 'bg-teal-700 text-white' : 'border border-brand-200 bg-white text-brand-700 hover:bg-brand-50' }}">
            {{ t('admin.translations.missing_all', 'Any missing') }}
            <span class="ms-1 opacity-80">({{ $missingCounts['all'] }})</span>
        </a>
        <a href="{{ route('admin.translations.missing', ['locale' => 'ps']) }}"
           class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ $locale === 'ps' ? 'bg-teal-700 text-white' : 'border border-brand-200 bg-white text-brand-700 hover:bg-brand-50' }}">
            {{ t('admin.translations.missing_ps_tab', 'Missing Pashto') }}
            <span class="ms-1 opacity-80">({{ $missingCounts['ps'] }})</span>
        </a>
        <a href="{{ route('admin.translations.missing', ['locale' => 'fa']) }}"
           class="rounded-xl px-4 py-2 text-sm font-semibold transition {{ $locale === 'fa' ? 'bg-teal-700 text-white' : 'border border-brand-200 bg-white text-brand-700 hover:bg-brand-50' }}">
            {{ t('admin.translations.missing_fa_tab', 'Missing Farsi') }}
            <span class="ms-1 opacity-80">({{ $missingCounts['fa'] }})</span>
        </a>
    </div>

    {{-- Filters --}}
    <form method="GET" class="mb-6 flex flex-wrap items-center gap-2">
        @if ($locale !== 'all')
            <input type="hidden" name="locale" value="{{ $locale }}">
        @endif
        <input type="text" name="q" value="{{ $q }}" placeholder="{{ t('admin.translations.search_placeholder', 'Search key or translation…') }}"
               class="w-56 rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
        <select name="group" aria-label="{{ t('admin.translations.all_groups', 'Group') }}" class="rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
            <option value="">{{ t('admin.translations.all_groups', 'All groups') }}</option>
            @foreach ($groups as $groupOption)
                <option value="{{ $groupOption }}" @selected($group === $groupOption)>{{ $groupOption }}</option>
            @endforeach
        </select>
        <button type="submit" class="rounded-xl bg-brand-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-800">{{ t('admin.common.filter', 'Filter') }}</button>
        @if (request()->filled('q') || request()->filled('group') || $locale !== 'all')
            <a href="{{ route('admin.translations.missing') }}" class="text-sm font-semibold text-brand-500 hover:text-brand-700">{{ t('admin.translations.clear', 'Clear') }}</a>
        @endif
    </form>

    <div class="overflow-x-auto rounded-2xl border border-brand-100 bg-white shadow-sm">
        <table class="w-full min-w-[760px] text-sm">
            <thead>
                <tr class="border-b border-brand-100 bg-brand-50 text-left text-xs font-semibold uppercase tracking-wide text-brand-500">
                    <th class="px-4 py-3">{{ t('admin.translations.key', 'Key') }}</th>
                    <th class="px-4 py-3">{{ t('admin.translations.english', 'English') }}</th>
                    <th class="px-4 py-3">{{ t('admin.translations.missing_locales', 'Missing') }}</th>
                    <th class="px-4 py-3 text-right">{{ t('admin.common.actions', 'Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse ($translations as $translation)
                    <tr class="transition hover:bg-brand-50/50">
                        <td class="px-4 py-3">
                            <p class="font-mono text-xs font-semibold text-brand-900">{{ $translation->key }}</p>
                            <span class="mt-1 inline-block rounded-full bg-teal-50 px-2.5 py-0.5 text-[11px] font-semibold text-teal-700">{{ $translation->group }}</span>
                        </td>
                        <td class="px-4 py-3 text-brand-600">{{ $translation->en ? \Illuminate\Support\Str::limit($translation->en, 80) : '—' }}</td>
                        <td class="px-4 py-3">
                            <div class="flex flex-wrap gap-1.5">
                                @if (!filled($translation->ps))
                                    <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700">{{ t('locales.ps.native', 'Pashto') }} {{ t('admin.translations.is_missing', 'missing') }}</span>
                                @endif
                                @if (!filled($translation->fa))
                                    <span class="rounded-full bg-gold-100 px-2.5 py-1 text-xs font-semibold text-gold-700">{{ t('locales.fa.native', 'Farsi') }} {{ t('admin.translations.is_missing', 'missing') }}</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end">
                                <a href="{{ route('admin.translations.edit', $translation) }}" class="rounded-lg border border-brand-200 px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:bg-brand-50">{{ t('admin.translations.fill_in', 'Fill in') }}</a>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="4" class="px-4 py-10 text-center">
                        <p class="text-brand-400">{{ t('admin.translations.nothing_missing', 'Nothing missing — every key is translated. 🎉') }}</p>
                    </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $translations->links() }}</div>
@endsection
