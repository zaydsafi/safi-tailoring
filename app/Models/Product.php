<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Product extends Model
{
    protected $fillable = [
        'category_id', 'name', 'slug', 'short_description', 'description', 'type',
        'fabric', 'sizes', 'price', 'sale_price', 'stock', 'track_stock',
        'is_featured', 'is_active', 'image',
        'name_translations', 'short_description_translations', 'description_translations',
    ];

    protected function casts(): array
    {
        return [
            'sizes' => 'array',
            'price' => 'decimal:2',
            'sale_price' => 'decimal:2',
            'is_featured' => 'boolean',
            'is_active' => 'boolean',
            'track_stock' => 'boolean',
            'name_translations' => 'array',
            'short_description_translations' => 'array',
            'description_translations' => 'array',
        ];
    }

    public function getNameAttribute($value): ?string
    {
        return (($this->name_translations ?? [])[app()->getLocale()] ?? null) ?: $value;
    }

    public function getShortDescriptionAttribute($value): ?string
    {
        return (($this->short_description_translations ?? [])[app()->getLocale()] ?? null) ?: $value;
    }

    public function getDescriptionAttribute($value): ?string
    {
        return (($this->description_translations ?? [])[app()->getLocale()] ?? null) ?: $value;
    }

    protected static function booted(): void
    {
        static::saving(function (Product $product) {
            if (blank($product->slug)) {
                $base = Str::slug($product->name);
                $slug = $base;
                $i = 1;
                while (static::where('slug', $slug)->where('id', '!=', $product->id ?? 0)->exists()) {
                    $slug = $base . '-' . $i++;
                }
                $product->slug = $slug;
            }
        });
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function images()
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function effectivePrice(): float
    {
        return (float) ($this->sale_price ?? $this->price);
    }

    public function onSale(): bool
    {
        return $this->sale_price !== null && (float) $this->sale_price < (float) $this->price;
    }

    public function inStock(int $qty = 1): bool
    {
        if (!$this->track_stock) {
            return true;
        }

        return $this->stock >= $qty;
    }

    public function decrementStock(int $qty): void
    {
        if ($this->track_stock) {
            $this->decrement('stock', $qty);
        }
    }
}
