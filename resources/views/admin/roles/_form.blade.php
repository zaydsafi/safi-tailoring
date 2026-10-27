@php
    $role = $role ?? new \App\Models\Role();
    $generalPermissions = \App\Support\Permissions::general();
    $tailorPermissions = \App\Support\Permissions::tailor();
    $tailorNotes = \App\Support\Permissions::TAILOR_NOTES;
    $isEdit = $role->exists;
    $isBuiltIn = $role->isBuiltIn();
    $selected = $role->permissions->pluck('key')->all();
@endphp

<div class="grid gap-5">
    <div class="grid gap-5 sm:grid-cols-2">
        <div>
            <label for="name" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.roles.role_name', 'Role name') }} <span class="text-red-500">*</span></label>
            <input type="text" id="name" name="name" value="{{ old('name', $role->name) }}" required placeholder="{{ t('admin.roles.name_placeholder', 'e.g. Manager, Tailor…)') }}"
                   class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('name') border-red-400 @enderror">
            @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
        </div>

        <div>
            <label for="description" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.roles.description', 'Description') }}</label>
            <input type="text" id="description" name="description" value="{{ old('description', $role->description) }}" placeholder="{{ t('admin.roles.description_hint', 'What is this role for?') }}"
                   class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('description') border-red-400 @enderror">
        </div>
    </div>

    <div>
        <p class="mb-1.5 text-sm font-medium text-brand-900">{{ t('admin.roles.permissions_title', 'Admin sections this role can manage') }}</p>
        <p class="mb-3 text-xs text-brand-400">{{ t('admin.roles.permissions_hint', 'Leave everything unchecked for a “view nothing” account, e.g. panel-login-only staff.') }}</p>
        <div class="grid gap-2 sm:grid-cols-2">
            @foreach ($generalPermissions as $key => $label)
                <label class="flex items-center gap-3 rounded-xl border border-brand-100 bg-brand-50/40 px-4 py-3 text-sm transition hover:bg-brand-50 {{ $isBuiltIn ? 'cursor-not-allowed opacity-50' : 'cursor-pointer' }}">
                    <input type="checkbox" name="permissions[]" value="{{ $key }}"
                           @if ($isBuiltIn) disabled class="pointer-events-none" @checked(true) @else class="h-4 w-4 rounded border-brand-300 text-teal-700 focus:ring-teal-600/30" @checked(in_array($key, $selected) || in_array($key, (array) old('permissions', []))) @endif>
                    <span class="font-medium text-brand-900">{{ $label }}</span>
                </label>
            @endforeach
        </div>
        @if ($isBuiltIn)
            <p class="mt-2 text-xs text-brand-400">{{ t('admin.roles.builtin_note', 'The Admin role is built in — it always has full access and its permissions cannot be edited.') }}</p>
        @endif
    </div>

    <div>
        <p class="mb-1.5 text-sm font-medium text-brand-900">{{ t('admin.roles.tailor_section_title', 'Tailor dashboard') }}</p>
        <p class="mb-3 text-xs text-brand-400">{{ t('admin.roles.tailor_section_hint', 'Workspace permissions for tailors: the tailor dashboard plus the custom-order queue.') }}</p>
        <div class="grid gap-2">
            @foreach ($tailorPermissions as $key => $label)
                <label class="rounded-xl border border-brand-100 bg-brand-50/40 px-4 py-3 text-sm transition hover:bg-brand-50 {{ $isBuiltIn ? 'cursor-not-allowed opacity-50' : 'cursor-pointer' }}">
                    <span class="flex items-center gap-3">
                        <input type="checkbox" name="permissions[]" value="{{ $key }}"
                               @if ($isBuiltIn) disabled class="pointer-events-none" @checked(true) @else class="h-4 w-4 rounded border-brand-300 text-teal-700 focus:ring-teal-600/30" @checked(in_array($key, $selected) || in_array($key, (array) old('permissions', []))) @endif>
                        <span class="font-medium text-brand-900">{{ $label }}</span>
                    </span>
                    <span class="mt-1.5 block pl-7 text-xs leading-relaxed text-brand-400">{{ t($tailorNotes[$key], $tailorNotes[$key]) }}</span>
                </label>
            @endforeach
        </div>
    </div>
</div>
