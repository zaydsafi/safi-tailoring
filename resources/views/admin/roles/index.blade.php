@extends('layouts.admin')

@section('title', t('admin.roles.title', 'Roles & Permissions'))

@section('content')
    <div class="mb-6 flex flex-col gap-3 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold text-brand-900">{{ t('admin.roles.title', 'Roles & Permissions') }}</h1>
            <p class="mt-1 text-sm text-brand-500">{{ t('admin.roles.subtitle', 'Create staff roles and choose which admin sections each one can manage.') }}</p>
        </div>
        <a href="{{ route('admin.roles.create') }}"
           class="inline-flex items-center justify-center gap-2 rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">
            <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4.5v15m7.5-7.5h-15"/></svg>
            {{ t('admin.roles.add', 'Add Role') }}
        </a>
    </div>

    <div class="overflow-hidden rounded-2xl border border-brand-100 bg-white shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[820px] text-sm">
                <thead>
                    <tr class="border-b border-brand-100 bg-brand-50 text-left text-xs font-semibold uppercase tracking-wide text-brand-500">
                        <th class="px-4 py-3">{{ t('admin.roles.role', 'Role') }}</th>
                        <th class="px-4 py-3">{{ t('admin.roles.permissions', 'Can manage') }}</th>
                        <th class="px-4 py-3">{{ t('admin.roles.users', 'Users') }}</th>
                        <th class="px-4 py-3 text-right">{{ t('admin.common.actions', 'Actions') }}</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-brand-50">
                    @forelse ($roles as $role)
                        <tr class="transition hover:bg-brand-50/50">
                            <td class="px-4 py-3">
                                <p class="font-semibold text-brand-900">{{ $role->name }}</p>
                                <p class="mt-0.5 text-xs text-brand-400">
                                    <span class="font-mono">{{ $role->slug }}</span>
                                    @if ($role->description)<span class="mx-1">·</span>{{ $role->description }}@endif
                                </p>
                            </td>
                            <td class="px-4 py-3">
                                @if ($role->isBuiltIn())
                                    <div class="flex flex-wrap gap-1.5">
                                        <span class="rounded-full bg-brand-900 px-2.5 py-1 text-xs font-semibold text-white">{{ t('admin.roles.everything', '⭐ Full access') }}</span>
                                        <span class="rounded-full bg-brand-50 px-2.5 py-1 text-xs text-brand-400">{{ t('admin.roles.builtin', 'built-in') }}</span>
                                    </div>
                                @else
                                    <div class="flex flex-wrap gap-1.5">
                                        @forelse ($role->permissions as $permission)
                                            <span class="rounded-full bg-teal-50 px-2.5 py-1 text-xs font-semibold text-teal-700">{{ $permission->label }}</span>
                                        @empty
                                            <span class="rounded-full bg-gray-100 px-2.5 py-1 text-xs font-semibold text-gray-500">{{ t('admin.roles.no_permissions', 'No permissions — panel login only') }}</span>
                                        @endforelse
                                    </div>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                <span class="{{ $userCounts->get($role->slug, 0) > 0 ? 'font-semibold text-brand-900' : 'text-brand-400' }}">
                                    {{ $userCounts->get($role->slug, 0) }}
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <div class="flex justify-end gap-2">
                                    <a href="{{ route('admin.roles.edit', $role) }}" class="rounded-lg border border-brand-200 px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:bg-brand-50">
                                        {{ $role->isBuiltIn() ? t('admin.roles.rename', 'Rename') : t('admin.common.edit', 'Edit') }}
                                    </a>
                                    @unless ($role->isBuiltIn())
                                        <form method="POST" action="{{ route('admin.roles.destroy', $role) }}"
                                              onsubmit="return confirm('{!! t('admin.roles.confirm_delete', 'Delete the role &quot;:name&quot;?', ['name' => e($role->name)]) !!}')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50">{{ t('admin.common.delete', 'Delete') }}</button>
                                        </form>
                                    @endunless
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4" class="px-4 py-10 text-center text-brand-400">{{ t('admin.roles.no_results', 'No roles yet.') }}</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
@endsection
