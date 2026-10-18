<x-mail::message>
# {{ $headline }}

{{ $message }}

{{ t('mail.order_track_cta', 'You can always check your order status here:') }}

<x-mail::button :url="route('orders.track')">
{{ t('mail.order_track_button', 'Track my order') }}
</x-mail::button>

Thanks,<br>
{{ $shopName }}
</x-mail::message>
