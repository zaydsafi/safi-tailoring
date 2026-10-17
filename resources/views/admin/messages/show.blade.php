@extends('layouts.admin')

@section('title', t('admin.messages.message', 'Message'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.messages.index') }}" class="text-sm font-semibold text-brand-500 hover:text-brand-700">{{ t('admin.messages.back_to_messages', '← Back to messages') }}</a>
    </div>

    <div class="max-w-3xl rounded-2xl border border-brand-100 bg-white p-6 shadow-sm sm:p-8">
        @php
            $shopName = \App\Models\Setting::get('shop_name', 'Safi Tailoring Shop');
            $shopEmail = \App\Models\Setting::get('shop_email');
            $shopPhone = \App\Models\Setting::get('shop_phone');

            $replySubject = t('admin.messages.reply_subject', 'Re: your message to :shop', ['shop' => $shopName]);
            $replyBody = t('admin.messages.reply_greeting', 'Dear :name,', ['name' => $message->name])
                . "\r\n\r\n"
                . t('admin.messages.reply_opening', 'Thank you for contacting :shop on :date. Here is what you wrote:', ['shop' => $shopName, 'date' => $message->created_at->format('j M Y, H:i')])
                . "\r\n"
                . '> ' . str_replace(["\r\n", "\r", "\n"], "\r\n> ", trim($message->message))
                . "\r\n\r\n"
                . t('admin.common.kind_regards', 'Kind regards')
                . "\r\n"
                . $shopName
                . ($shopEmail ? "\r\n" . $shopEmail : '')
                . ($shopPhone ? "\r\n" . $shopPhone : '');

            $whatsapp = (function (string $phone): string {
                $digits = preg_replace('/\D/', '', $phone);

                // Local Kabul format (09XXXXXXXX / 7XXXXXXXX) → international: +93…
                return match (true) {
                    str_starts_with($digits, '0') => '93' . substr($digits, 1),
                    default => $digits,
                };
            })($message->phone);

            $replyHref = 'mailto:' . rawurlencode($message->email ?: '') . http_build_query(['subject' => $replySubject, 'body' => $replyBody], '', '&', PHP_QUERY_RFC3986);
        @endphp

        <div class="mb-6 flex flex-wrap items-start justify-between gap-3 border-b border-brand-100 pb-5">
            <div>
                <h2 class="text-lg font-bold text-brand-900">{{ \Illuminate\Support\Str::limit($message->message, 60) }}</h2>
                <p class="mt-1 text-sm text-brand-500">{{ t('admin.messages.from', 'From') }} <span class="font-semibold text-brand-900">{{ $message->name }}</span>
                    @if ($message->email)
                        · {{ $message->email }}
                    @endif
                    · {{ $message->created_at->locale(app()->getLocale())->translatedFormat('M j, Y H:i') }}
                </p>
            </div>
            <div class="flex flex-wrap gap-2">
                @if ($message->email)
                    <a href="{{ $replyHref }}" class="rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">{{ t('admin.messages.reply_by_email', 'Reply by Email') }}</a>
                @else
                    <span class="rounded-xl border border-brand-200 bg-brand-50 px-4 py-2.5 text-sm font-semibold text-brand-400">{{ t('admin.messages.no_email_left', 'Customer left no email') }}</span>
                @endif
                @if ($message->phone)
                    <a href="tel:{{ $message->phone }}" class="rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">{{ t('admin.messages.call_customer', 'Call') }} {{ $message->phone }}</a>
                    <a href="https://wa.me/{{ $whatsapp }}" target="_blank" rel="noopener" class="rounded-xl border border-green-200 bg-white px-4 py-2.5 text-sm font-semibold text-green-700 transition hover:bg-green-50">{{ t('admin.messages.whatsapp_customer', 'WhatsApp') }}</a>
                @endif
            </div>
        </div>
        <div class="whitespace-pre-line text-sm leading-relaxed text-brand-700">{{ $message->message }}</div>

        <form method="POST" action="{{ route('admin.messages.destroy', $message) }}" class="mt-8 border-t border-brand-100 pt-5" onsubmit="return confirm('{{ t('admin.messages.confirm_delete', 'Delete this message?') }}')">
            @csrf
            @method('DELETE')
            <button type="submit" class="rounded-xl border border-red-300 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50">{{ t('admin.messages.delete_message', 'Delete Message') }}</button>
        </form>
    </div>
@endsection
