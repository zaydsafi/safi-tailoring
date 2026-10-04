@extends('layouts.admin')

@section('title', 'Users')

@section('content')
    <div class="mb-6 flex flex-col gap-3 lg:flex-row lg:items-center lg:justify-between">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search name, email or phone…"
                   class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 sm:w-64">
            <select name="role"
                    class="rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                <option value="">All roles</option>
                <option value="admin" @selected(request('role') === 'admin')>Admins</option>
                <option value="customer" @selected(request('role') === 'customer')>Customers</option>
            </select>
            <select name="status"
                    class="rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                <option value="">All statuses</option>
                <option value="active" @selected(request('status') === 'active')>Active</option>
                <option value="blocked" @selected(request('status') === 'blocked')>Blocked</option>
            </select>
            <button type="submit" class="rounded-xl bg-brand-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-800">Filter</button>
            @if (request()->filled('q') || request()->filled('role') || request()->filled('status'))
                <a href="{{ route('admin.users.index') }}" class="text-sm font-semibold text-brand-500 hover:text-brand-700">Clear</a>
            @endif
        </form>
        <a href="{{ route('admin.users.create') }}"
           class="inline-flex shrink-0 items-center justify-center gap-2 rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            Add User
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-brand-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-sm">
                <thead>
                    <tr class="border-b border-brand-100 bg-brand-50 text-left text-xs font-semibold uppercase tracking-wide text-brand-500">
                        <th class="px-4 py-3">User</th>
                        <th class="px-4 py-3">Contact</th>
                        <th class="px-4 py-3">Role</th>
                        <th class="px-4 py-3">Status</th>
                        <th class="px-4 py-3">Orders</th>
                        <th class="px-4 py-3">Joined</th>
                        <th class="px-4 py-3 text-right">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-50">
                    @forelse ($users as $user)
                        <tr class="transition hover:bg-brand-50/50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <span class="flex h-9 w-9 shrink-0 items-center justify-center rounded-full {{ $user->isAdmin() ? 'bg-brand-900' : 'bg-teal-700' }} text-xs font-bold text-white">{{ strtoupper(substr($user->name, 0, 1)) }}</span>
                                    <div class="min-w-0">
                                        <p class="truncate font-semibold text-brand-900">{{ $user->name }}</p>
                                        @if ($user->is(auth()->user()))
                                            <p class="text-xs text-brand-400">That's you</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <p>{{ $user->email }}</p>
                                <p class="text-xs text-brand-400">{{ $user->phone ?? '—' }}</p>
                            </td>
                            <td class="px-4 py-3">
                                <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $user->isAdmin() ? 'bg-brand-900 text-white' : 'bg-brand-100 text-brand-700' }}">
                                    {{ $user->isAdmin() ? 'Admin' : 'Customer' }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                @if ($user->isBlocked())
                                    <span class="rounded-full bg-red-100 px-2.5 py-1 text-xs font-semibold text-red-700" title="{{ $user->blocked_reason ?? 'Blocked ' . $user->blocked_at->format('M j, Y') }}">Blocked</span>
                                @else
                                    <span class="rounded-full bg-green-100 px-2.5 py-1 text-xs font-semibold text-green-700">Active</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">{{ $user->orders_count }}</td>
                            <td class="px-4 py-3 text-brand-600">{{ $user->created_at->format('M j, Y') }}</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center justify-end gap-2">
                                    @if (! $user->isAdmin())
                                        <a href="{{ route('admin.customers.show', $user) }}" class="rounded-lg border border-brand-200 px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:bg-brand-50">View</a>
                                        @if ($user->isBlocked())
                                            <form method="POST" action="{{ route('admin.users.unblock', $user) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-lg border border-green-300 px-3 py-1.5 text-xs font-semibold text-green-700 transition hover:bg-green-50">Unblock</button>
                                            </form>
                                        @else
                                            <form method="POST" action="{{ route('admin.users.block', $user) }}"
                                                  onsubmit="return confirm('Block {{ $user->name }}? They will be signed out and cannot sign in again until unblocked.')">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="rounded-lg border border-amber-300 px-3 py-1.5 text-xs font-semibold text-amber-700 transition hover:bg-amber-50">Block</button>
                                            </form>
                                        @endif
                                    @endif
                                    <a href="{{ route('admin.users.edit', $user) }}" class="rounded-lg border border-brand-200 px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:bg-brand-50">Edit</a>
                                    @unless ($user->is(auth()->user()))
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                              onsubmit="return confirm('Delete this user? Their past orders are kept for records.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50">Delete</button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="px-4 py-10 text-center text-brand-400">No users found.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <div class="mt-6">{{ $users->links() }}</div>
@endsection
