<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;

class ShopController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::active()->orderBy('sort_order')->get();

        $products = Product::active()
            ->with('category')
            ->when($request->filled('category'), function ($q) use ($request) {
                $q->whereHas('category', fn ($c) => $c->where('slug', $request->query('category')));
            })
            ->when($request->filled('q'), function ($q) use ($request) {
                $search = $request->query('q');
                $q->where(fn ($w) => $w
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('fabric', 'like', "%{$search}%")
                    ->orWhere('name_translations->ps', 'like', "%{$search}%")
                    ->orWhere('name_translations->fa', 'like', "%{$search}%"));
            })
            ->when($request->filled('type'), function ($q) use ($request) {
                $q->where('type', $request->query('type'));
            })
            ->when($request->query('sort') === 'price_asc', fn ($q) => $q->orderByRaw('COALESCE(sale_price, price) asc'))
            ->when($request->query('sort') === 'price_desc', fn ($q) => $q->orderByRaw('COALESCE(sale_price, price) desc'))
            ->when($request->query('sort') === 'oldest', fn ($q) => $q->oldest())
            ->when(!in_array($request->query('sort'), ['price_asc', 'price_desc', 'oldest']), fn ($q) => $q->latest())
            ->paginate(12)
            ->withQueryString();

        return view('shop.index', compact('products', 'categories'));
    }

    public function show(string $slug)
    {
        $product = Product::active()->with(['category', 'images'])->where('slug', $slug)->firstOrFail();
        $related = Product::active()->where('category_id', $product->category_id)->where('id', '!=', $product->id)->take(4)->get();

        return view('shop.show', compact('product', 'related'));
    }
}
