<?php

namespace App\Http\Middleware;

use App\Support\Locales;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\URL;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    public function handle(Request $request, Closure $next): Response
    {
        $locale = $request->route('locale');

        if (! is_string($locale) || ! array_key_exists($locale, Locales::SUPPORTED)) {
            $locale = Locales::DEFAULT;
        }

        app()->setLocale($locale);
        URL::defaults(['locale' => $locale]);

        if ($request->session()->get('locale') !== $locale) {
            $request->session()->put('locale', $locale);
        }

        // Controllers receive route parameters positionally, so the {locale}
        // segment must be dropped after it has served its purpose here.
        $request->route()->forgetParameter('locale');

        return $next($request);
    }
}
