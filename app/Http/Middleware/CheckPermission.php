<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckPermission
{
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('admin.login')->with('error', 'Please sign in first.');
        }

        if (! $user->canManage($permission)) {
            abort(403, 'You do not have permission to access this section.');
        }

        return $next($request);
    }
}
