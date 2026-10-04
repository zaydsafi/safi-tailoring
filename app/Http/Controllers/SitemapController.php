<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Support\Locales;

class SitemapController extends Controller
{
    /**
     * XML sitemap: one <url> entry per locale for every public page
     * (home, shop, about, contact) and every active product. Each entry
     * carries the full set of hreflang alternates plus x-default,
     * which always points to the /en version.
     */
    public function index()
    {
        $locales = Locales::codes();

        $urls = [];

        foreach (['home', 'shop.index', 'about', 'contact'] as $routeName) {
            $alternates = $this->alternates(
                $locales,
                fn (string $locale) => route($routeName, ['locale' => $locale])
            );

            foreach ($locales as $locale) {
                $urls[] = [
                    'loc' => $alternates[$locale],
                    'alternates' => $alternates,
                    'lastmod' => null,
                ];
            }
        }

        Product::query()
            ->active()
            ->orderBy('id')
            ->chunk(100, function ($products) use (&$urls, $locales) {
                foreach ($products as $product) {
                    $alternates = $this->alternates(
                        $locales,
                        fn (string $locale) => route('shop.show', ['locale' => $locale, 'slug' => $product->slug])
                    );

                    foreach ($locales as $locale) {
                        $urls[] = [
                            'loc' => $alternates[$locale],
                            'alternates' => $alternates,
                            'lastmod' => $product->updated_at?->toAtomString(),
                        ];
                    }
                }
            });

        return response()
            ->view('sitemap', ['urls' => $urls])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    /**
     * Robots.txt: block the admin panel and webhook endpoint, keep the
     * private/transactional locale-prefixed pages out of the index, then
     * allow the public storefront and advertise the sitemap.
     */
    public function robots()
    {
        $lines = [
            'User-agent: *',
            'Disallow: /admin',
            'Disallow: /webhooks',
            'Disallow: /*/cart',
            'Disallow: /*/checkout',
            'Disallow: /*/account',
            'Disallow: /*/payment',
            'Disallow: /*/order',
            'Disallow: /*/track-order',
            'Disallow: /*/login',
            'Disallow: /*/register',
            'Disallow: /*/forgot-password',
            'Disallow: /*/reset-password',
            'Allow: /',
            '',
            'Sitemap: ' . route('sitemap'),
        ];

        return response(implode("\n", $lines) . "\n", 200)
            ->header('Content-Type', 'text/plain');
    }

    /**
     * @param  array<int, string>  $locales
     * @param  callable(string): string  $resolver
     * @return array<string, string> locale => absolute URL, plus x-default
     */
    private function alternates(array $locales, callable $resolver): array
    {
        $alternates = [];

        foreach ($locales as $locale) {
            $alternates[$locale] = $resolver($locale);
        }

        $alternates['x-default'] = $alternates[Locales::DEFAULT];

        return $alternates;
    }
}
