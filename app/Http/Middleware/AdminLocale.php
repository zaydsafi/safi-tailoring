<?php

namespace App\Http\Middleware;

use App\Support\Locales;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = (string) session('admin_locale', Locales::DEFAULT);

        if (! array_key_exists($locale, Locales::SUPPORTED)) {
            $locale = Locales::DEFAULT;
        }

        app()->setLocale($locale);

        return $next($request);
    }
}
