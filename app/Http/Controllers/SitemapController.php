<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Setting;
use App\Support\Locales;
use Illuminate\Support\Str;

class SitemapController extends Controller
{
    /**
     * Google Merchant Center product feed: one plain-data item per active
     * product, ready to submit in Search Console / Merchant Center for
     * Google Shopping listings. Rendered in the shop's default language
     * because one feed file carries one language and the titles live in
     * the products' English base fields.
     */
    public function productsFeed()
    {
        app()->setLocale(Locales::DEFAULT);

        $products = Product::active()
            ->with('category')
            ->orderBy('id')
            ->get();

        $items = $products->map(fn (Product $product) => [
            'id' => 'st-'.$product->id,
            'title' => $product->name,
            'description' => $product->short_description
                ?: Str::limit(strip_tags((string) $product->description), 300),
            'link' => route('shop.show', ['locale' => Locales::DEFAULT, 'slug' => $product->slug]),
            'image' => $product->image ? url('storage/'.$product->image) : null,
            'availability' => $product->inStock() ? 'in_stock' : 'out_of_stock',
            'price' => number_format($product->effectivePrice(), 2, '.', ''),
            'currency' => Setting::get('currency_code', 'AFN') ?: 'AFN',
            'brand' => Setting::get('shop_name', 'Safi Tailoring Shop'),
        ])->all();

        return response()
            ->view('product-feed', ['items' => $items])
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

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

        foreach (['home', 'shop.index', 'about', 'contact', 'custom-order.request'] as $i => $routeName) {
            $alternates = $this->alternates(
                $locales,
                fn (string $locale) => route($routeName, ['locale' => $locale])
            );

            foreach ($locales as $locale) {
                $urls[] = [
                    'loc' => $alternates[$locale],
                    'alternates' => $alternates,
                    'lastmod' => null,
                    'priority' => $i === 0 ? '1.0' : '0.8',
                    'changefreq' => $i === 0 ? 'daily' : 'weekly',
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
                            'priority' => '0.8',
                            'changefreq' => 'weekly',
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
            'Sitemap: '.route('sitemap'),
        ];

        return response(implode("\n", $lines)."\n", 200)
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
