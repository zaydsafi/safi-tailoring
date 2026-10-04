@extends('layouts.admin')

@section('title', 'Customer: '.$customer->name)

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.users.index', ['role' => 'customer']) }}" class="text-sm font-semibold text-brand-500 hover:text-brand-700">← Back to users</a>
    </div>

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6">
            <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                <div class="mb-4 flex items-center gap-3">
                    <span class="flex h-12 w-12 items-center justify-center rounded-full bg-teal-700 text-lg font-bold text-white">{{ strtoupper(substr($customer->name, 0, 1)) }}</span>
                    <div>
                        <h2 class="flex flex-wrap items-center gap-2 text-base font-bold text-brand-900">
                            {{ $customer->name }}
                            @if ($customer->isBlocked())
                                <span class="rounded-full bg-red-100 px-2 py-0.5 text-xs font-semibold text-red-700">Blocked</span>
                            @endif
                        </h2>
                        <p class="text-xs text-brand-400">Customer since {{ $customer->created_at->format('M j, Y') }}</p>
                    </div>
                </div>
                <dl class="space-y-3 text-sm">
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-brand-400">Email</dt><dd class="mt-0.5 font-medium">{{ $customer->email }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-brand-400">Phone</dt><dd class="mt-0.5 font-medium">{{ $customer->phone ?? '—' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-brand-400">Address</dt><dd class="mt-0.5 font-medium">{{ $customer->address ?? '—' }}</dd></div>
                    <div><dt class="text-xs font-semibold uppercase tracking-wide text-brand-400">City</dt><dd class="mt-0.5 font-medium">{{ $customer->city ?? '—' }}</dd></div>
                </dl>
            </div>

            <div class="rounded-2xl border {{ $customer->isBlocked() ? 'border-red-200' : 'border-brand-100' }} bg-white p-6 shadow-sm">
                <h2 class="mb-2 text-base font-bold {{ $customer->isBlocked() ? 'text-red-700' : 'text-brand-900' }}">Account Access</h2>
                @if ($customer->isBlocked())
                    <p class="mb-1 text-sm text-brand-700">This account is blocked and cannot sign in. Their orders are kept.</p>
                    <p class="mb-4 text-xs text-brand-400">
                        Blocked {{ $customer->blocked_at->format('M j, Y H:i') }}
                        @if ($customer->blocked_reason) · Reason: {{ $customer->blocked_reason }} @endif
                    </p>
                    <form method="POST" action="{{ route('admin.users.unblock', $customer) }}">
                        @csrf
                        @method('PATCH')
                        <button type="submit" class="w-full rounded-xl bg-green-600 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-green-700">Unblock Account</button>
                    </form>
                @else
                    <p class="mb-4 text-xs text-brand-500">Blocking signs the customer out immediately and prevents them from signing in again until unblocked. Past orders are kept.</p>
                    <form method="POST" action="{{ route('admin.users.block', $customer) }}" class="space-y-3"
                          onsubmit="return confirm('Block {{ $customer->name }}? They will not be able to sign in.')">
                        @csrf
                        @method('PATCH')
                        <input type="text" name="reason" placeholder="Reason (optional, recorded for your team)"
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                        <button type="submit" class="w-full rounded-xl border border-red-300 px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50">Block Account</button>
                    </form>
                @endif
            </div>

            @if ($customer->measurementProfiles->count())
                <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm">
                    <h2 class="mb-4 text-base font-bold text-brand-900">Measurement Profiles ({{ $customer->measurementProfiles->count() }})</h2>
                    @foreach ($customer->measurementProfiles as $profile)
                        <div class="mb-3 rounded-xl border border-brand-100 p-4 last:mb-0">
                            <p class="mb-2 text-sm font-semibold text-brand-900">{{ $profile->label }}</p>
                            <div class="flex flex-wrap gap-1.5">
                                @foreach (\App\Models\MeasurementProfile::fields() as $field => $label)
                                    @if ($profile->$field !== null)
                                        <span class="rounded-lg bg-brand-50 px-2 py-1 text-xs font-medium text-brand-700">{{ $label }}: {{ $profile->$field }}″</span>
                                    @endif
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>

        <div class="min-w-0 xl:col-span-2">
            <h2 class="mb-4 text-base font-bold text-brand-900">Recent Orders ({{ $customer->orders->count() }})</h2>
            <div class="overflow-x-auto rounded-2xl border border-brand-100 bg-white shadow-sm">
                <table class="w-full min-w-[640px] text-sm">
                    <thead>
                        <tr class="border-b border-brand-100 bg-brand-50 text-left text-xs font-semibold uppercase tracking-wide text-brand-500">
                            <th class="px-4 py-3">Order</th>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3">Total</th>
                            <th class="px-4 py-3">Payment</th>
                            <th class="px-4 py-3">Status</th>
                            <th class="px-4 py-3 text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-brand-50">
                        @forelse ($customer->orders as $order)
                            <tr class="transition hover:bg-brand-50/50">
                                <td class="px-4 py-3 font-semibold text-teal-700">{{ $order->order_number }}</td>
                                <td class="px-4 py-3 text-brand-600">{{ $order->created_at->format('M j, Y') }}</td>
                                <td class="px-4 py-3 font-semibold">{{ money($order->total) }}</td>
                                <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ status_badge_class($order->payment_status) }}">{{ ucfirst($order->payment_status) }}</span></td>
                                <td class="px-4 py-3"><span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ status_badge_class($order->status) }}">{{ $order->statusLabel() }}</span></td>
                                <td class="px-4 py-3 text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="rounded-lg border border-brand-200 px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:bg-brand-50">Manage</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-10 text-center text-brand-400">This customer has no orders yet.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
