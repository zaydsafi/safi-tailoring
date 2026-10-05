@extends('layouts.admin')

@section('title', t('admin.messages.title', 'Messages'))

@section('content')
    <div class="mb-6">
        <p class="text-sm text-brand-500">{{ $unreadCount === 1 ? t('admin.messages.unread_one', ':count unread message', ['count' => $unreadCount]) : t('admin.messages.unread_many', ':count unread messages', ['count' => $unreadCount]) }}</p>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-brand-100 bg-white shadow-sm">
        <table class="w-full min-w-[720px] text-sm">
            <thead>
                <tr class="border-b border-brand-100 bg-brand-50 text-left text-xs font-semibold uppercase tracking-wide text-brand-500">
                    <th class="px-4 py-3">{{ t('admin.messages.from', 'From') }}</th>
                    <th class="px-4 py-3">{{ t('admin.messages.subject', 'Subject') }}</th>
                    <th class="px-4 py-3">{{ t('admin.messages.received', 'Received') }}</th>
                    <th class="px-4 py-3">{{ t('admin.common.status', 'Status') }}</th>
                    <th class="px-4 py-3 text-right">{{ t('admin.common.actions', 'Actions') }}</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse ($messages as $message)
                    <tr class="transition hover:bg-brand-50/50 {{ $message->is_read ? '' : 'bg-gold-50/40' }}">
                        <td class="px-4 py-3">
                            <p class="font-semibold {{ $message->is_read ? 'text-brand-700' : 'text-brand-900' }}">{{ $message->name }}</p>
                            <p class="text-xs text-brand-400">{{ $message->email }}</p>
                        </td>
                        <td class="px-4 py-3">{{ $message->subject ?? t('admin.messages.no_subject', 'No subject') }}</td>
                        <td class="px-4 py-3 text-brand-600">{{ $message->created_at->locale(app()->getLocale())->translatedFormat('M j, Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $message->is_read ? 'bg-gray-100 text-gray-500' : 'bg-gold-100 text-gold-800' }}">
                                {{ $message->is_read ? t('admin.messages.read', 'Read') : t('admin.messages.unread', 'Unread') }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.messages.show', $message) }}" class="rounded-lg border border-brand-200 px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:bg-brand-50">{{ t('admin.messages.open', 'Open') }}</a>
                                <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" onsubmit="return confirm('{{ t('admin.messages.confirm_delete', 'Delete this message?') }}')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50">{{ t('admin.common.delete', 'Delete') }}</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-brand-400">{{ t('admin.messages.no_messages', 'No messages yet.') }}</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $messages->links() }}</div>
@endsection
