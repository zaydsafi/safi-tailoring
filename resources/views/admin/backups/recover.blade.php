@extends('layouts.admin')

@section('title', t('admin.backups.recover_title', 'Recover data'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.backups.index') }}" class="text-sm font-semibold text-brand-500 hover:text-brand-700">{{ t('admin.backups.back_to_backups', '← Back to backups') }}</a>
    </div>

    <div class="max-w-3xl rounded-2xl border border-brand-100 bg-white p-6 shadow-sm sm:p-8">
        <h2 class="text-lg font-bold text-brand-900">{{ t('admin.backups.recover_title', 'Recover data') }}</h2>
        <p class="mt-1 text-sm text-brand-500">{{ t('admin.backups.recover_desc', 'Choose which tables to recover from this backup. Selected tables are emptied and filled with the backup contents — every other table keeps its current data.') }}</p>
        <p class="mt-1 font-mono text-xs text-brand-400">{{ $name }}</p>

        <form method="POST" action="{{ route('admin.backups.restore', $name) }}" class="mt-6">
            @csrf
            <div class="overflow-hidden rounded-xl border border-brand-100">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="border-b border-brand-100 bg-brand-50 text-start text-xs font-semibold text-brand-500 uppercase tracking-wide">
                            <th class="px-4 py-2.5 text-start">{{ t('admin.backups.table_column', 'Table') }}</th>
                            <th class="px-4 py-2.5 text-start">{{ t('admin.backups.rows_column', 'Rows in backup') }}</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-50">
                        @forelse ($tables as $table => $rows)
                            <tr class="transition hover:bg-brand-50/50">
                                <td class="px-4 py-2.5">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" name="tables[]" value="{{ $table }}" checked
                                               class="h-4 w-4 rounded border-brand-300 text-teal-700 focus:ring-teal-600/30">
                                        <span class="font-mono text-xs text-brand-900">{{ $table }}</span>
                                    </label>
                                </td>
                                <td class="px-4 py-2.5 text-brand-500">{{ $rows }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="2" class="px-4 py-8 text-center text-brand-400">{{ t('admin.backups.no_tables', 'This dump contains no table data.') }}</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <p class="mt-3 text-xs text-brand-400">{{ t('admin.backups.recover_warning', 'Untick the boxes you do NOT want to overwrite. Untouched tables are never modified.') }}</p>

            <div class="mt-6 flex justify-end gap-3 border-t border-brand-100 pt-5">
                <a href="{{ route('admin.backups.index') }}" class="rounded-xl border border-brand-200 px-4 py-2.5 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">{{ t('admin.common.cancel', 'Cancel') }}</a>
                <button type="submit"
                        onsubmit=""
                        class="rounded-xl bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800"
                        onclick="return confirm('{!! t('admin.backups.confirm_recover', 'Recover the selected tables from :name? Ticked tables are emptied first and filled with the backup contents.', ['name' => e($name)]) !!}')">
                    {{ t('admin.backups.recover_submit', 'Recover selected tables') }}
                </button>
            </div>
        </form>
    </div>
@endsection
