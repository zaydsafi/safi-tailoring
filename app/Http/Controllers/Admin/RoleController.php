<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use App\Support\Permissions;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class RoleController extends Controller
{
    public function index()
    {
        $roles = Role::with('permissions')->orderBy('id')->get();

        $userCounts = User::query()
            ->selectRaw('role, count(*) as total')
            ->whereIn('role', $roles->pluck('slug'))
            ->groupBy('role')
            ->pluck('total', 'role');

        return view('admin.roles.index', compact('roles', 'userCounts'));
    }

    public function create()
    {
        return view('admin.roles.create', [
            'role' => new Role(),
            'permissions' => Permissions::CATALOG,
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        $role = Role::create($data);
        $role->permissions()->sync($this->permissionIds($request));

        return redirect()->route('admin.roles.index')
            ->with('success', t('admin.flash.role_created', 'Role ":name" created.', ['name' => $role->name]));
    }

    public function edit(Role $role)
    {
        return view('admin.roles.edit', [
            'role' => $role->load('permissions'),
            'permissions' => Permissions::CATALOG,
        ]);
    }

    public function update(Request $request, Role $role)
    {
        $data = $this->validated($request, $role);

        $originalSlug = $role->slug;

        $role->update($data);

        if (! $role->isBuiltIn()) {
            $role->permissions()->sync($this->permissionIds($request));
        }

        // Keep member accounts in sync when a role is renamed.
        if ($data['slug'] !== $originalSlug) {
            User::query()->where('role', $originalSlug)->update(['role' => $data['slug']]);
        }

        return redirect()->route('admin.roles.index')
            ->with('success', t('admin.flash.role_updated', 'Role ":name" updated.', ['name' => $role->name]));
    }

    public function destroy(Role $role)
    {
        if ($role->isBuiltIn()) {
            return back()->with('error', t('admin.flash.role_builtin', 'The Admin role cannot be deleted.'));
        }

        $userCount = User::query()->where('role', $role->slug)->count();

        if ($userCount > 0) {
            return back()->with('error', t('admin.flash.role_in_use',
                'This role is assigned to :count user(s). Move them to another role first.',
                ['count' => $userCount]));
        }

        $role->delete();

        return back()->with('success', t('admin.flash.role_deleted', 'Role deleted.'));
    }

    /**
     * Convert the posted permission keys to IDs for the pivot table.
     */
    private function permissionIds(Request $request): array
    {
        $keys = array_intersect(
            (array) $request->input('permissions', []),
            Permissions::keys()
        );

        return \App\Models\Permission::query()->whereIn('key', $keys)->pluck('id')->all();
    }

    private function validated(Request $request, ?Role $role = null): array
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:500'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => [Rule::in(Permissions::keys())],
        ]);

        $data['slug'] = Str::slug($data['name']) ?: Str::random(8);

        // The slug is derived, not posted — merge it in so the unique
        // rule actually has something to validate.
        $request->merge(['slug' => $data['slug']]);

        $slugRule = Rule::unique('roles', 'slug');
        if ($role) {
            $slugRule = $slugRule->ignore($role);
        }

        $request->validate(['slug' => ['required', 'string', 'max:100', $slugRule]]);

        return $data;
    }
}
