<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CatalogSeeder extends Seeder
{
    private array $palette = [
        ['#1e3a5f', '#3b82f6'], ['#5f1e2e', '#ef4444'], ['#1e5f3a', '#22c55e'],
        ['#54401a', '#f59e0b'], ['#3a1e5f', '#a855f7'], ['#1e4d5f', '#06b6d4'],
        ['#5f1e4d', '#ec4899'], ['#404040', '#9ca3af'], ['#5f3a1e', '#d97706'],
    ];

    public function run(): void
    {
        Storage::disk('public')->makeDirectory('products');
        Storage::disk('public')->makeDirectory('categories');

        $categories = [
            ['name' => 'Men\'s Shalwar Kameez', 'description' => 'Classic and embroidered shalwar kameez sets for men, stitched to perfection.'],
            ['name' => 'Women\'s Dresses', 'description' => 'Elegant embroidered dresses and three-piece suits for women.'],
            ['name' => 'Suits & Blazers', 'description' => 'Tailored suits and blazers with premium fitting for formal occasions.'],
            ['name' => 'Kurtas', 'description' => 'Casual and festive kurtas in quality fabrics.'],
            ['name' => 'Alteration Services', 'description' => 'Professional fitting and alteration services for your garments.'],
        ];

        $categoryIds = [];
        foreach ($categories as $index => $cat) {
            $image = $this->placeholder('categories', Str::slug($cat['name']), $cat['name'], $index);
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($cat['name'])],
                [
                    'name' => $cat['name'],
                    'description' => $cat['description'],
                    'image' => $image,
                    'is_active' => true,
                    'sort_order' => $index,
                ]
            );
            $categoryIds[$cat['name']] = $category->id;
        }

        $products = [
            // Men's Shalwar Kameez
            ['category' => 'Men\'s Shalwar Kameez', 'name' => 'Classic White Shalwar Kameez', 'type' => 'custom', 'price' => 2500, 'fabric' => 'Premium Cotton', 'sizes' => null, 'featured' => true, 'stock' => 0],
            ['category' => 'Men\'s Shalwar Kameez', 'name' => 'Navy Embroidered Kameez Set', 'type' => 'ready', 'price' => 3200, 'sale_price' => 2800, 'fabric' => 'Wash & Wear', 'sizes' => ['S', 'M', 'L', 'XL', 'XXL'], 'featured' => true, 'stock' => 20],
            ['category' => 'Men\'s Shalwar Kameez', 'name' => 'Black Formal Shalwar Kameez', 'type' => 'ready', 'price' => 2900, 'fabric' => 'Soft Cotton', 'sizes' => ['M', 'L', 'XL'], 'featured' => false, 'stock' => 15],
            ['category' => 'Men\'s Shalwar Kameez', 'name' => 'Grey Designer Kameez Set', 'type' => 'custom', 'price' => 3800, 'fabric' => 'Jacquard', 'sizes' => null, 'featured' => false, 'stock' => 0],
            // Women's Dresses
            ['category' => 'Women\'s Dresses', 'name' => 'Embroidered Three-Piece Suit', 'type' => 'ready', 'price' => 5500, 'sale_price' => 4900, 'fabric' => 'Lawn with Embroidery', 'sizes' => ['S', 'M', 'L'], 'featured' => true, 'stock' => 12],
            ['category' => 'Women\'s Dresses', 'name' => 'Bridal Dress (Made to Order)', 'type' => 'custom', 'price' => 25000, 'fabric' => 'Silk & Hand Embroidery', 'sizes' => null, 'featured' => true, 'stock' => 0],
            ['category' => 'Women\'s Dresses', 'name' => 'Printed Summer Dress', 'type' => 'ready', 'price' => 3200, 'fabric' => 'Printed Lawn', 'sizes' => ['S', 'M', 'L', 'XL'], 'featured' => false, 'stock' => 25],
            ['category' => 'Women\'s Dresses', 'name' => 'Chiffon Party Wear Dress', 'type' => 'custom', 'price' => 7500, 'fabric' => 'Chiffon', 'sizes' => null, 'featured' => false, 'stock' => 0],
            // Suits & Blazers
            ['category' => 'Suits & Blazers', 'name' => 'Two-Piece Business Suit', 'type' => 'custom', 'price' => 18000, 'fabric' => 'Wool Blend', 'sizes' => null, 'featured' => true, 'stock' => 0],
            ['category' => 'Suits & Blazers', 'name' => 'Classic Navy Blazer', 'type' => 'ready', 'price' => 8500, 'fabric' => 'Polyester Blend', 'sizes' => ['46', '48', '50', '52'], 'featured' => false, 'stock' => 8],
            ['category' => 'Suits & Blazers', 'name' => 'Formal Waistcoat Set', 'type' => 'ready', 'price' => 4500, 'sale_price' => 3900, 'fabric' => 'Poly Viscose', 'sizes' => ['M', 'L', 'XL'], 'featured' => false, 'stock' => 18],
            // Kurtas
            ['category' => 'Kurtas', 'name' => 'Casual Cotton Kurta', 'type' => 'ready', 'price' => 1800, 'fabric' => 'Cotton', 'sizes' => ['S', 'M', 'L', 'XL'], 'featured' => false, 'stock' => 30],
            ['category' => 'Kurtas', 'name' => 'Festive Embroidered Kurta', 'type' => 'ready', 'price' => 2600, 'fabric' => 'Cotton Silk', 'sizes' => ['M', 'L', 'XL'], 'featured' => true, 'stock' => 22],
            // Alteration Services
            ['category' => 'Alteration Services', 'name' => 'Trouser / Jeans Alteration', 'type' => 'service', 'price' => 300, 'fabric' => null, 'sizes' => null, 'featured' => false, 'stock' => 0],
            ['category' => 'Alteration Services', 'name' => 'Shirt / Kameez Fitting', 'type' => 'service', 'price' => 400, 'fabric' => null, 'sizes' => null, 'featured' => false, 'stock' => 0],
            ['category' => 'Alteration Services', 'name' => 'Suit Resizing & Pressing', 'type' => 'service', 'price' => 800, 'fabric' => null, 'sizes' => null, 'featured' => false, 'stock' => 0],
        ];

        foreach ($products as $i => $p) {
            $slug = Str::slug($p['name']);
            $image = $this->placeholder('products', $slug, $p['name'], $i);

            Product::updateOrCreate(
                ['slug' => $slug],
                [
                    'category_id' => $categoryIds[$p['category']],
                    'name' => $p['name'],
                    'short_description' => 'Quality ' . ($p['fabric'] ?: 'service') . ' by Safi Tailoring Shop.',
                    'description' => $p['name'] . ' — expertly crafted by Safi Tailoring Shop. '
                        . ($p['type'] === 'custom' ? 'Made to your exact measurements. Our master tailors cut and stitch this piece after you place your order. '
                            : ($p['type'] === 'service' ? 'Bring your garment to our shop and we will take care of the rest. '
                            : 'Ready to wear, available off the rack in the listed sizes. '))
                        . 'Fabric: ' . ($p['fabric'] ?: 'N/A') . '.',
                    'type' => $p['type'],
                    'fabric' => $p['fabric'],
                    'sizes' => $p['sizes'],
                    'price' => $p['price'],
                    'sale_price' => $p['sale_price'] ?? null,
                    'stock' => $p['stock'],
                    'track_stock' => $p['type'] === 'ready',
                    'is_featured' => $p['featured'],
                    'is_active' => true,
                    'image' => $image,
                ]
            );
        }
    }

    /**
     * Write a simple branded SVG placeholder into the public disk and return its storage path.
     */
    private function placeholder(string $dir, string $slug, string $label, int $colorIndex): string
    {
        $path = $dir . '/' . $slug . '.svg';
        $disk = Storage::disk('public');
        $abs = $disk->path($path);

        if (!File::exists($abs)) {
            [$bg, $fg] = $this->palette[$colorIndex % count($this->palette)];
            $lines = wordwrap($label, 22, "\n");
            $tspans = '';
            foreach (explode("\n", $lines) as $n => $line) {
                $dy = $n === 0 ? 0 : 34;
                $tspans .= '<tspan x="400" dy="' . $dy . '">' . htmlspecialchars($line, ENT_QUOTES) . '</tspan>';
            }
            $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="800" height="800" viewBox="0 0 800 800">'
                . '<rect width="800" height="800" fill="' . $bg . '"/>'
                . '<circle cx="400" cy="330" r="120" fill="' . $fg . '" opacity="0.25"/>'
                . '<text x="400" y="340" text-anchor="middle" font-family="Georgia, serif" font-size="30" fill="#ffffff">' . $tspans . '</text>'
                . '<text x="400" y="470" text-anchor="middle" font-family="Georgia, serif" font-size="20" fill="' . $fg . '">SAFI TAILORING SHOP</text>'
                . '</svg>';
            $disk->put($path, $svg);
        }

        return $path;
    }
}
