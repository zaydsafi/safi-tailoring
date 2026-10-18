<x-mail::message>
# {{ t('mail.otp_title', 'Your verification code') }}

{{ $name ? t('mail.otp_hi', 'Hi :name,', ['name' => $name]) . ' ' : '' }}{{ t('mail.otp_body', 'Use this code to finish creating your account:') }}

<x-mail::panel>
## {{ $code }}
</x-mail::panel>

{{ t('mail.otp_valid', 'This code is valid for :minutes minutes. If you did not request it, you can ignore this email.', ['minutes' => $ttl]) }}

{{ t('mail.otp_share', 'Never share this code with anyone — the shop will never ask for it.') }}

Thanks,<br>
{{ config('mail.from.name') }}
</x-mail::message>
