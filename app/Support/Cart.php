<?php

namespace App\Support;

use App\Models\Product;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class Cart
{
    private const SESSION_KEY = 'cart.items';

    /**
     * @return array<int, array{key: string, product_id: int, qty: int, size: ?string, measurements: ?array}>
     */
    public function items(): array
    {
        return session(self::SESSION_KEY, []);
    }

    public function add(int $productId, int $qty = 1, ?string $size = null, ?array $measurements = null): void
    {
        $items = $this->items();

        foreach ($items as &$item) {
            if ($item['product_id'] === $productId && $item['size'] === $size && $item['measurements'] == $measurements) {
                $item['qty'] += $qty;
                $this->save($items);
                return;
            }
        }

        $items[] = [
            'key' => (string) Str::uuid(),
            'product_id' => $productId,
            'qty' => $qty,
            'size' => $size,
            'measurements' => $measurements,
        ];

        $this->save($items);
    }

    public function update(string $key, int $qty): void
    {
        $items = $this->items();

        foreach ($items as &$item) {
            if ($item['key'] === $key) {
                $item['qty'] = max(1, $qty);
                break;
            }
        }

        $this->save($items);
    }

    public function remove(string $key): void
    {
        $items = array_values(array_filter($this->items(), fn ($item) => $item['key'] !== $key));
        $this->save($items);
    }

    public function clear(): void
    {
        session()->forget(self::SESSION_KEY);
    }

    public function count(): int
    {
        return array_sum(array_column($this->items(), 'qty'));
    }

    public function isEmpty(): bool
    {
        return $this->count() === 0;
    }

    /**
     * Cart lines joined with their products. Stale entries (deleted/inactive products) are dropped.
     */
    public function content(): Collection
    {
        $items = $this->items();

        if (empty($items)) {
            return collect();
        }

        $products = Product::with('category')->whereIn('id', array_column($items, 'product_id'))->get()->keyBy('id');

        return collect($items)
            ->map(function ($item) use ($products) {
                $product = $products->get($item['product_id']);

                if (!$product || !$product->is_active) {
                    return null;
                }

                $price = $product->effectivePrice();

                return (object) [
                    'key' => $item['key'],
                    'product' => $product,
                    'qty' => $item['qty'],
                    'size' => $item['size'],
                    'measurements' => $item['measurements'],
                    'price' => $price,
                    'line_total' => $price * $item['qty'],
                ];
            })
            ->filter()
            ->values();
    }

    public function subtotal(): float
    {
        return (float) $this->content()->sum('line_total');
    }

    public function save(array $items): void
    {
        session()->put(self::SESSION_KEY, $items);
    }
}
