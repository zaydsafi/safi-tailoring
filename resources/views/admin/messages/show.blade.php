@extends('layouts.admin')

@section('title', t('admin.messages.message', 'Message'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.messages.index') }}" class="text-sm font-semibold text-brand-500 hover:text-brand-700">{{ t('admin.messages.back_to_messages', '← Back to messages') }}</a>
    </div>

    <div class="max-w-3xl rounded-2xl border border-brand-100 bg-white p-6 shadow-sm sm:p-8">
        <div class="mb-6 flex flex-wrap items-start justify-between gap-3 border-b border-brand-100 pb-5">
            <div>
                <h2 class="text-lg font-bold text-brand-900">{{ $message->subject ?? t('admin.messages.no_subject', 'No subject') }}</h2>
                <p class="mt-1 text-sm text-brand-500">{{ t('admin.messages.from', 'From') }} <span class="font-semibold text-brand-900">{{ $message->name }}</span> · {{ $message->email }} · {{ $message->created_at->locale(app()->getLocale())->translatedFormat('M j, Y H:i') }}</p>
            </div>
            <a href="mailto:{{ $message->email }}?subject=Re: {{ $message->subject ?? t('admin.messages.your_message', 'Your message') }}" class="rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">{{ t('admin.messages.reply_by_email', 'Reply by Email') }}</a>
        </div>
        <div class="whitespace-pre-line text-sm leading-relaxed text-brand-700">{{ $message->message }}</div>

        <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" class="mt-8 border-t border-brand-100 pt-5" onsubmit="return confirm('{{ t('admin.messages.confirm_delete', 'Delete this message?') }}')">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-xl border border-red-300 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50">{{ t('admin.messages.delete_message', 'Delete Message') }}</button>
        </form>
    </div>
@endsection
