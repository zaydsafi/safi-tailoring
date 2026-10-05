@extends('layouts.admin')

@section('title', t('admin.translations.title', 'Translations'))

@section('content')
    {{-- Stats --}}
    <div class="grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
        <div class="rounded-2xl border border-brand-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-brand-500">{{ t('admin.translations.total', 'Total Translations') }}</p>
            <p class="mt-2 text-2xl font-bold text-brand-900">{{ $totalCount }}</p>
        </div>
        <div class="rounded-2xl border border-brand-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-brand-500">{{ t('admin.translations.missing_ps', 'Missing Pashto') }}</p>
            <p class="mt-2 text-2xl font-bold {{ $missingPs > 0 ? 'text-gold-600' : 'text-brand-900' }}">{{ $missingPs }}</p>
        </div>
        <div class="rounded-2xl border border-brand-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-brand-500">{{ t('admin.translations.missing_fa', 'Missing Farsi') }}</p>
            <p class="mt-2 text-2xl font-bold {{ $missingFa > 0 ? 'text-gold-600' : 'text-brand-900' }}">{{ $missingFa }}</p>
        </div>
        <div class="rounded-2xl border border-brand-100 bg-white p-5 shadow-sm">
            <p class="text-sm font-medium text-brand-500">{{ t('admin.translations.groups', 'Groups') }}</p>
            <p class="mt-2 text-2xl font-bold text-brand-900">{{ $groups->count() }}</p>
        </div>
    </div>

    {{-- Filters + add --}}
    <div class="mt-6 mb-6 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="text" name="q" value="{{ $q }}" placeholder="{{ t('admin.translations.search_placeholder', 'Search key or translation…') }}"
                   class="w-56 rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
            <select name="group" class="rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                <option value="">{{ t('admin.translations.all_groups', 'All groups') }}</option>
                @foreach ($groups as $groupOption)
                    <option value="{{ $groupOption }}" @selected($group === $groupOption)>{{ $groupOption }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-xl bg-brand-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-800">{{ t('admin.common.filter', 'Filter') }}</button>
            @if (request()->filled('q') || request()->filled('group'))
                <a href="{{ route('admin.translations.index') }}" class="text-sm font-semibold text-brand-500 hover:text-brand-700">{{ t('admin.translations.clear', 'Clear') }}</a>
            @endif
        </form>
        <a href="{{ route('admin.translations.create') }}" class="rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">{{ t('admin.translations.add', '+ Add Translation') }}</a>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-brand-100 bg-white shadow-sm">
        <table class="w-full min-w-[880px] text-sm">
            <thead>
                <tr class="border-b border-brand-100 bg-brand-50 text-left text-xs font-semibold uppercase tracking-wide text-brand-500">
                    <th class="px-4 py-3">{{ t('admin.translations.key', 'Key') }}</th>
                    <th class="px-4 py-3">{{ t('admin.translations.english', 'English') }}</th>
                    <th class="px-4 py-3">{{ t('admin.translations.pashto', 'Pashto') }}</th>
                    <th class="px-4 py-3">{{ t('admin.translations.farsi', 'Farsi') }}</th>
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
                        <td class="px-4 py-3 text-brand-600">{{ $translation->en ? \Illuminate\Support\Str::limit($translation->en, 60) : '—' }}</td>
                        <td class="px-4 py-3">
                            @if (filled($translation->ps))
                                <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800">{{ t('admin.translations.set', '✓ Set') }}</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500">{{ t('admin.translations.missing', 'missing') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if (filled($translation->fa))
                                <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-800">{{ t('admin.translations.set', '✓ Set') }}</span>
                            @else
                                <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500">{{ t('admin.translations.missing', 'missing') }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.translations.edit', $translation) }}" class="rounded-lg border border-brand-200 px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:bg-brand-50">{{ t('admin.common.edit', 'Edit') }}</a>
                                <form method="POST" action="{{ route('admin.translations.destroy', $translation) }}" onsubmit="return confirm('{!! t('admin.translations.confirm_delete', 'Delete translation &quot;:key&quot;? This cannot be undone.', ['key' => e($translation->key)]) !!}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50">{{ t('admin.common.delete', 'Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-brand-400">{{ t('admin.translations.no_results', 'No translations found.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $translations->links() }}</div>
@endsection
