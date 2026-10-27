@extends('layouts.admin')

@section('title', t('admin.email_log.title', 'Email Log'))

@section('content')
    <div class="mb-6">
        <div class="flex flex-wrap items-start justify-between gap-3">
            <div>
                <p class="max-w-2xl text-sm text-brand-500">{{ t('admin.email_log.subtitle', 'Outgoing email attempts: order mail, verification codes and the delivery test. A failed row shows the exact error the mail server returned.') }}</p>
                @if ($failedCount > 0)
                    <p class="mt-1 text-sm font-semibold text-red-600">
                        {{ t('admin.email_log.failed_count', ':count failed attempts', ['count' => $failedCount]) }}
                    </p>
                @endif
            </div>
            <a href="{{ route('admin.exports.email_log') }}"
               class="rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">{{ t('admin.reports.export_excel', 'Export (Excel)') }}</a>
        </div>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-brand-100 bg-white shadow-sm">
        <table class="w-full min-w-[720px] text-sm">
            <thead>
                <tr class="border-b border-brand-100 bg-brand-50 text-start text-xs font-semibold text-brand-500 uppercase tracking-wide">
                    <th class="px-4 py-3 text-start">{{ t('admin.email_log.time', 'Time') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('admin.email_log.recipient', 'Recipient') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('admin.email_log.subject', 'Subject') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('admin.email_log.type', 'Type') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('admin.email_log.status', 'Status') }}</th>
                    <th class="px-4 py-3 text-start">{{ t('admin.email_log.error', 'Error') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse ($deliveries as $delivery)
                    <tr class="align-top transition hover:bg-brand-50/50 {{ $delivery->failed() ? 'bg-red-50/50' : '' }}">
                        <td class="px-4 py-3 whitespace-nowrap text-brand-600">{{ $delivery->created_at->locale(app()->getLocale())->translatedFormat('M j, Y H:i') }}</td>
                        <td class="px-4 py-3 break-all text-brand-700">{{ $delivery->recipient }}</td>
                        <td class="px-4 py-3 max-w-[260px] break-words text-brand-900">{{ $delivery->subject ?? t('admin.messages.no_subject', 'No subject') }}</td>
                        <td class="px-4 py-3 text-brand-500">
                            {{ $delivery->mailable_class ? str($delivery->mailable_class)->afterLast('\\') : '—' }}
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $delivery->failed() ? 'bg-red-100 text-red-700' : 'bg-green-100 text-green-700' }}">
                                {{ $delivery->failed()
                                    ? t('admin.email_log.failed', 'Failed')
                                    : t('admin.email_log.sent', 'Sent') }}
                            </span>
                        </td>
                        <td class="px-4 py-3 max-w-[420px] break-words font-mono text-xs text-red-600">
                            {{ $delivery->error ?? '—' }}
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center text-brand-400">{{ t('admin.email_log.empty', 'No email attempts yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $deliveries->links() }}</div>
@endsection
