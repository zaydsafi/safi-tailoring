<?php

namespace App\Exports;

use App\Models\Product;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;

/**
 * @implements FromQuery<Product>
 */
class ProductsExport implements FromQuery, WithHeadings, WithMapping
{
    use Exportable;

    public function query(): Builder
    {
        return Product::query()->with('category')->orderByDesc('id');
    }

    public function headings(): array
    {
        return [
            'ID', 'Name', 'Slug', 'Category', 'Type', 'Fabric',
            'Price', 'Sale Price', 'Stock', 'Tracks Stock', 'Featured', 'Active',
        ];
    }

    /**
     * @param  Product  $product
     */
    public function map($product): array
    {
        return [
            $product->id,
            $product->name,
            $product->slug,
            $product->category?->name,
            ucfirst((string) $product->type),
            $product->fabric,
            (float) $product->price,
            $product->sale_price !== null ? (float) $product->sale_price : null,
            (int) $product->stock,
            $product->track_stock ? 'yes' : 'no',
            $product->is_featured ? 'yes' : 'no',
            $product->is_active ? 'yes' : 'no',
        ];
    }
}
