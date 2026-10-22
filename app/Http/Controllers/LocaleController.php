<?php

namespace App\Http\Controllers;

use App\Support\Locales;
use Illuminate\Http\Request;

/**
 * Locale redirect helpers. Kept as controller methods (not route closures)
 * so the route cache can be enabled for production deployments.
 */
class LocaleController extends Controller
{
    /**
     * Root: send visitors to their preferred language.
     */
    public function root(Request $request)
    {
        $locale = (string) $request->session()->get('locale', Locales::DEFAULT);

        return redirect('/'.(array_key_exists($locale, Locales::SUPPORTED) ? $locale : Locales::DEFAULT));
    }

    /**
     * Admin language switch: persists the choice in the session and returns
     * to the previous admin page (or the dashboard when there is none).
     */
    public function switchAdmin(Request $request, string $locale)
    {
        if (array_key_exists($locale, Locales::SUPPORTED)) {
            $request->session()->put('admin_locale', $locale);
        }

        $previous = url()->previous();

        // Only go back if we came from an admin page (and not the switcher
        // itself); otherwise (fresh session, external referer) land on the
        // admin dashboard.
        if (str_starts_with(parse_url($previous, PHP_URL_PATH) ?? '/', '/admin') && $previous !== url()->current()) {
            return redirect($previous);
        }

        return redirect(route('admin.dashboard'));
    }
}
