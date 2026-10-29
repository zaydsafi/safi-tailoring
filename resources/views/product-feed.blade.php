{!! '<?xml version="1.0" encoding="UTF-8"?>' !!}
<rss version="2.0" xmlns:g="http://base.google.com/ns/1.0">
    <channel>
        <title>{{ \App\Models\Setting::get('shop_name', 'Safi Tailoring Shop') }}</title>
        <link>{{ url('/') }}</link>
        <description>{{ \App\Models\Setting::get('shop_tagline', '') }}</description>
@foreach ($items as $item)
        <item>
            <g:id>{{ $item['id'] }}</g:id>
            <g:title>{{ $item['title'] }}</g:title>
            <g:description>{{ $item['description'] }}</g:description>
            <g:link>{{ $item['link'] }}</g:link>
@if ($item['image'])
            <g:image_link>{{ $item['image'] }}</g:image_link>
@endif
            <g:condition>new</g:condition>
            <g:availability>{{ $item['availability'] }}</g:availability>
            <g:price>{{ $item['price'] }} {{ $item['currency'] }}</g:price>
            <g:brand>{{ $item['brand'] }}</g:brand>
        </item>
@endforeach
    </channel>
</rss>
