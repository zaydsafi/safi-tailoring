<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Support\WhatsApp;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $users = User::withCount('orders')
            ->when($request->filled('q'), function ($query) use ($request) {
                $q = '%' . $request->string('q')->trim() . '%';
                $query->where(fn ($sub) => $sub
                    ->where('name', 'like', $q)
                    ->orWhere('email', 'like', $q)
                    ->orWhere('phone', 'like', $q));
            })
            ->when(in_array($request->input('role'), ['admin', 'customer'], true),
                fn ($query) => $query->where('role', $request->input('role')))
            ->when($request->input('status') === 'blocked', fn ($query) => $query->whereNotNull('blocked_at'))
            ->when($request->input('status') === 'active', fn ($query) => $query->whereNull('blocked_at'))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.users.index', compact('users'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        User::create($this->validated($request));

        return redirect()->route('admin.users.index')->with('success', 'User created.');
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $this->validated($request, $user);

        if ($request->user()->is($user) && $data['role'] !== $user->role) {
            return back()->withInput()->with('error', 'You cannot change your own role.');
        }

        if ($user->isAdmin() && $data['role'] !== 'admin' && User::where('role', 'admin')->count() === 1) {
            return back()->withInput()->with('error', 'You cannot demote the only administrator account.');
        }

        if (blank($data['password'] ?? null)) {
            unset($data['password']);
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User updated.');
    }

    public function destroy(Request $request, User $user)
    {
        if ($request->user()->is($user)) {
            return back()->with('error', 'You cannot delete your own account.');
        }

        if ($user->isAdmin() && User::where('role', 'admin')->count() === 1) {
            return back()->with('error', 'You cannot delete the only administrator account.');
        }

        $user->delete();

        return back()->with('success', 'User deleted. Their past orders are kept for records.');
    }

    public function block(Request $request, User $user)
    {
        if ($user->isAdmin()) {
            return back()->with('error', 'Admin accounts cannot be blocked.');
        }

        if ($user->isBlocked()) {
            return back()->with('error', 'This account is already blocked.');
        }

        $data = $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        $user->forceFill([
            'blocked_at' => now(),
            'blocked_reason' => $data['reason'] ?: null,
            'remember_token' => null,
        ])->save();

        return back()->with('success', $user->name . '\'s account has been blocked. They can no longer sign in.');
    }

    public function unblock(User $user)
    {
        if (! $user->isBlocked()) {
            return back()->with('error', 'This account is not blocked.');
        }

        $user->forceFill([
            'blocked_at' => null,
            'blocked_reason' => null,
        ])->save();

        return back()->with('success', $user->name . '\'s account has been unblocked. They can sign in again.');
    }

    private function validated(Request $request, ?User $user = null): array
    {
        $request->merge([
            'phone' => WhatsApp::normalize($request->input('phone')) ?? $request->input('phone'),
        ]);

        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'phone' => ['nullable', 'string', 'max:30', Rule::unique('users', 'phone')->ignore($user)],
            'role' => ['required', Rule::in(['admin', 'customer'])],
            'address' => ['nullable', 'string', 'max:255'],
            'city' => ['nullable', 'string', 'max:255'],
            'password' => [$user ? 'nullable' : 'required', 'string', 'min:8', 'confirmed'],
        ]);
    }
}
