<?php

namespace App\Http\Middleware;

use App\Models\Role;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = auth()->user();

        // Admin accounts pass as before. Staff accounts get in only when
        // their `role` string matches a row in the roles table — plain
        // shop customers ('customer') never match and stay out.
        $staff = $user !== null && $user->role !== 'customer'
            && Role::query()->where('slug', $user->role)->exists();

        if (!auth()->check() || (!auth()->user()->isAdmin() && !$staff)) {
            return redirect()->route('admin.login')->with('error', 'Please sign in with an admin account.');
        }

        return $next($request);
    }
}
