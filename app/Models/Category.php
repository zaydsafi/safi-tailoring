<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Category extends Model
{
    protected $fillable = ['name', 'slug', 'description', 'image', 'is_active', 'sort_order', 'name_translations', 'description_translations'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'name_translations' => 'array',
            'description_translations' => 'array',
        ];
    }

    public function getNameAttribute($value): ?string
    {
        return (($this->name_translations ?? [])[app()->getLocale()] ?? null) ?: $value;
    }

    public function getDescriptionAttribute($value): ?string
    {
        return (($this->description_translations ?? [])[app()->getLocale()] ?? null) ?: $value;
    }

    protected static function booted(): void
    {
        static::saving(function (Category $category) {
            if (blank($category->slug)) {
                $category->slug = Str::slug($category->name);
            }
        });
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
