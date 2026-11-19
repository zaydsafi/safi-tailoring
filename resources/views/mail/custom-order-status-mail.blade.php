<x-mail::message>
# Your custom order request has been updated

Your custom order request (**#{{ $request->id }}**) is now **{{ $statusLabel }}**.

@if($request->customer_phone)
**Phone:** {{ $request->customer_phone }}
@endif

@if($request->customer_address)
**Address:** {{ $request->customer_address }}
@endif

@if($body)
{{ $body }}
@endif

If you have any updates or questions, contact us on WhatsApp or phone.

<x-mail::button :url="\App\Support\WhatsApp::link($shopSettings['shop_phone'], 'Hi! Question about my custom order request #' . $request->id) ?? 'https://wa.me/'">
Contact the shop
</x-mail::button>

Thanks,<br>
{{ $shopSettings['shop_name'] ?? 'Safi Tailoring Shop' }}
</x-mail::message>
