@extends('layouts.admin')

@section('title', 'Order '.$order->order_number)

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <div class="flex items-center gap-3">
            <span class="rounded-full px-3 py-1.5 text-sm font-semibold {{ status_badge_class($order->status) }}">{{ $order->statusLabel() }}</span>
            <span class="rounded-full px-3 py-1.5 text-sm font-semibold {{ status_badge_class($order->payment_status) }}">Payment: {{ ucfirst($order->payment_status) }}</span>
        </div>
        <a href="{{ route('admin.orders.index') }}" class="text-sm font-semibold text-brand-500 hover:text-brand-700">← Back to orders</a>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            {{-- Items --}}
            <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-bold text-brand-900">Items ({{ $order->items->count() }})</h2>
                <div class="space-y-4">
                    @foreach ($order->items as $item)
                        <div class="rounded-xl border border-brand-100 p-4">
                            <div class="flex flex-wrap items-start justify-between gap-2">
                                <div>
                                    <p class="font-semibold text-brand-900">
                                        @if ($item->product)
                                            <a href="{{ route('admin.products.edit', $item->product) }}" class="text-teal-700 hover:underline">{{ $item->product_name }}</a>
                                        @else
                                            {{ $item->product_name }}
                                        @endif
                                    </p>
                                    <p class="mt-0.5 text-xs text-brand-400">
                                        {{ ucfirst($item->type) }}
                                        @if ($item->size) · Size: {{ $item->size }} @endif
                                        · Qty: {{ $item->quantity }}
                                    </p>
                                </div>
                                <p class="font-bold text-brand-900">{{ money($item->line_total) }}</p>
                            </div>
                            @if (!empty($item->measurements))
                                <div class="mt-3 flex flex-wrap gap-1.5">
                                    @foreach ($item->measurements as $field => $value)
                                        <span class="rounded-lg bg-brand-50 px-2.5 py-1 text-xs font-medium text-brand-700">{{ ucfirst(str_replace('_', ' ', $field)) }}: {{ $value }}″</span>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
                <dl class="mt-5 space-y-2 border-t border-brand-100 pt-4 text-sm">
                    <div class="flex justify-between"><dt class="text-brand-500">Subtotal</dt><dd class="font-semibold">{{ money($order->subtotal) }}</dd></div>
                    <div class="flex justify-between"><dt class="text-brand-500">Delivery Fee</dt><dd class="font-semibold">{{ $order->delivery_fee > 0 ? money($order->delivery_fee) : 'Free' }}</dd></div>
                    <div class="flex justify-between text-base"><dt class="font-bold text-brand-900">Total</dt><dd class="font-bold text-brand-900">{{ money($order->total) }}</dd></div>
                </dl>
            </div>

            {{-- Status history --}}
            <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-bold text-brand-900">Status History</h2>
                <ol class="relative space-y-4 border-l-2 border-brand-100 pl-5">
                    @foreach ($order->statusHistories as $history)
                        <li class="relative">
                            <span class="absolute -left-[27px] top-1 h-3 w-3 rounded-full border-2 border-white {{ $history->status === 'cancelled' ? 'bg-red-500' : 'bg-teal-600' }}"></span>
                            <p class="text-sm font-semibold text-brand-900">{{ $order::STATUSES[$history->status] ?? ucfirst($history->status) }}</p>
                            <p class="text-xs text-brand-400">{{ $history->created_at->format('M j, Y H:i') }}</p>
                            @if ($history->note)
                                <p class="mt-1 rounded-lg bg-brand-50 px-3 py-2 text-xs text-brand-600">{{ $history->note }}</p>
                            @endif
                        </li>
                    @endforeach
                </ol>
            </div>
        </div>

        <div class="space-y-6">
            {{-- Customer --}}
            <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-bold text-brand-900">Customer</h2>
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-brand-400">Name</dt><dd class="mt-0.5 font-medium text-brand-900">{{ $order->customer_name }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-brand-400">Phone</dt><dd class="mt-0.5 font-medium text-brand-900">{{ $order->customer_phone }}</dd></div>
                    @if ($order->customer_email)
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-brand-400">Email</dt><dd class="mt-0.5 font-medium text-brand-900">{{ $order->customer_email }}</dd></div>
                    @endif
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-brand-400">Address</dt><dd class="mt-0.5 font-medium text-brand-900">{{ $order->customer_address }}</dd></div>
                    @if ($order->customer_city)
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-brand-400">City</dt><dd class="mt-0.5 font-medium text-brand-900">{{ $order->customer_city }}</dd></div>
                    @endif
                    @if ($order->notes)
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-brand-400">Order Notes</dt><dd class="mt-0.5 rounded-lg bg-brand-50 px-3 py-2 text-brand-700">{{ $order->notes }}</dd></div>
                    @endif
                    @if ($order->user)
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-brand-400">Account</dt><dd class="mt-0.5"><a href="{{ route('admin.customers.show', $order->user) }}" class="font-semibold text-teal-700 hover:underline">Registered customer</a></dd></div>
                    @else
                        <div><dt class="text-xs font-semibold uppercase tracking-wide text-brand-400">Account</dt><dd class="mt-0.5 text-brand-500">Guest checkout</dd></div>
                    @endif
                </dl>

                @php $waCustomer = \App\Support\WhatsApp::link($order->customer_phone, 'Hello ' . $order->customer_name . '! This is ' . $shopSettings['shop_name'] . ' about your order ' . $order->order_number . '.'); @endphp
                @if ($waCustomer)
                    <a href="{{ $waCustomer }}" target="_blank" rel="noopener"
                       class="mt-4 flex w-full items-center justify-center gap-2 rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700">
                        <svg class="h-4 w-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                        Chat on WhatsApp
                    </a>
                @endif
            </div>

            {{-- Update status --}}
            <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-bold text-brand-900">Update Status</h2>
                <form method="POST" action="{{ route('admin.orders.status', $order) }}" class="space-y-3">
                    @csrf
                    @method('PATCH')
                    <select name="status" class="w-full rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                        @foreach ($statuses as $value => $label)
                            <option value="{{ $value }}" @selected($order->status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <input type="text" name="note" placeholder="Note (optional, shown to customer)"
                           class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                    <button type="submit" class="w-full rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">Update Status</button>
                </form>
            </div>

            {{-- Update payment --}}
            <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                <h2 class="mb-4 text-base font-bold text-brand-900">Payment</h2>
                <form method="POST" action="{{ route('admin.orders.payment', $order) }}" class="flex gap-2">
                    @csrf
                    @method('PATCH')
                    <select name="payment_status" class="flex-1 rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                        @foreach (['pending' => 'Pending', 'paid' => 'Paid', 'refunded' => 'Refunded'] as $value => $label)
                            <option value="{{ $value }}" @selected($order->payment_status === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                    <button type="submit" class="rounded-xl bg-brand-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-800">Save</button>
                </form>
                <p class="mt-2 text-xs text-brand-400">
                    Method: {{ payment_method_label($order->payment_method) }}
                    @if ($order->payment_transaction_id) · TRX: {{ $order->payment_transaction_id }} @endif
                </p>
            </div>

            {{-- Danger zone --}}
            <div class="rounded-2xl border border-red-200 bg-white p-6 shadow-sm">
                <h2 class="mb-2 text-base font-bold text-red-700">Danger Zone</h2>
                <p class="mb-4 text-xs text-brand-500">Deleting an order removes it permanently. Stock is not restored.</p>
                <form method="POST" action="{{ route('admin.orders.destroy', $order) }}" onsubmit="return confirm('Delete order {{ $order->order_number }} permanently?')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="w-full rounded-xl border border-red-300 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50">Delete Order</button>
                </form>
            </div>
        </div>
    </div>
@endsection
