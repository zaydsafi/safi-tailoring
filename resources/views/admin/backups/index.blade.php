@extends('layouts.admin')

@section('title', t('admin.backups.title', 'Backups'))

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-brand-900">{{ t('admin.backups.title', 'Backups') }}</h1>
            <p class="mt-1 max-w-2xl text-sm text-brand-500">{{ t('admin.backups.subtitle', 'Full SQL dumps of the shop database in storage/app/backups. A nightly backup runs automatically; you can also create one anytime and download it to your own computer.') }}</p>
        </div>
        <form method="POST" action="{{ route('admin.backups.store') }}">
            @csrf
            <button type="submit"
                    class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
                {{ t('admin.backups.create', 'Create backup') }}
            </button>
        </form>
    </div>

    <div class="overflow-hidden rounded-2xl border border-brand-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[720px] text-sm">
                <thead>
                    <tr class="border-b border-brand-100 bg-brand-50 text-start text-xs font-semibold text-brand-500 uppercase tracking-wide">
                        <th class="px-4 py-3 text-start">{{ t('admin.backups.table_created', 'Created') }}</th>
                        <th class="px-4 py-3 text-start">{{ t('admin.backups.table_name', 'File') }}</th>
                        <th class="px-4 py-3 text-start">{{ t('admin.backups.table_size', 'Size') }}</th>
                        <th class="px-4 py-3 text-end">{{ t('admin.common.actions', 'Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-50">
                    @forelse ($backups as $backup)
                        <tr class="align-top transition hover:bg-brand-50/50">
                            <td class="px-4 py-3 whitespace-nowrap text-brand-600">
                                {{ \Illuminate\Support\Carbon::createFromTimestamp($backup['modified'])->locale(app()->getLocale())->translatedFormat('M j, Y H:i') }}
                            </td>
                            <td class="px-4 py-3 break-all font-mono text-xs text-brand-900">{{ $backup['name'] }}</td>
                            <td class="px-4 py-3 whitespace-nowrap text-brand-600">{{ number_format($backup['size'] / 1024, 1) }} KB</td>
                            <td class="px-4 py-3">
                                <div class="flex flex-wrap justify-end gap-2">
                                    <a href="{{ route('admin.backups.download', $backup['name']) }}"
                                       class="rounded-lg border border-brand-200 px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:bg-brand-50">{{ t('admin.backups.download', 'Download') }}</a>
                                    <a href="{{ route('admin.backups.recover', $backup['name']) }}"
                                       class="rounded-lg border border-teal-200 px-3 py-1.5 text-xs font-semibold text-teal-700 transition hover:bg-teal-50">{{ t('admin.backups.recover', 'Recover') }}</a>
                                    <form method="POST" action="{{ route('admin.backups.restore', $backup['name']) }}"
                                          onsubmit="return confirm('{!! t('admin.backups.confirm_restore', 'Replace ALL current shop data with the contents of ":name"? The data currently in the database is lost. This cannot be undone.', ['name' => e($backup['name'])]) !!}')">
                                        @csrf
                                        <button type="submit"
                                                class="rounded-lg border border-yellow-300 px-3 py-1.5 text-xs font-semibold text-yellow-700 transition hover:bg-yellow-50">{{ t('admin.backups.restore', 'Restore') }}</button>
                                    </form>
                                    <form method="POST" action="{{ route('admin.backups.destroy', $backup['name']) }}"
                                          onsubmit="return confirm('{!! t('admin.backups.confirm_delete', 'Delete the backup ":name"?', ['name' => e($backup['name'])]) !!}')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit"
                                                class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50">{{ t('admin.common.delete', 'Delete') }}</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-10 text-center text-brand-400">{{ t('admin.backups.empty', 'No backups yet — create the first one above.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <p class="mt-4 text-xs text-brand-400">{{ t('admin.backups.restore_warning', 'Restore replaces every table in the database with the contents of the backup file. Download a fresh backup right before restoring.') }}</p>
@endsection
