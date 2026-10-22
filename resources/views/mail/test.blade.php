<x-mail::message>
# {{ t('mail.test_email_headline', 'Your outgoing email is working') }}

{{ t('mail.test_email_body',
    'This is a delivery test from the shop settings. If you can read this, customers will receive order updates at this address.') }}

Thanks,<br>
{{ $shopName }}
</x-mail::message>
