@component('mail::message')
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

<p>If you have any updates or questions, contact us on WhatsApp or phone.</p>

@component('mail::button', ['url' => \App\Support\WhatsApp::link($shopSettings['shop_phone'], 'Hi! Question about my custom order request #' . $request->id) ?? 'https://wa.me/'])
Contact the shop
@endcomponent

Thanks,<br>
{{ $shopSettings['shop_name'] ?? 'Safi Tailoring Shop' }}
@endcomponent
