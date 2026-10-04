<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        $products = Product::with('category')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%' . $request->query('q') . '%'))
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->query('category')))
            ->latest()
            ->paginate(15)
            ->withQueryString();

        $filterCategories = Category::orderBy('sort_order')->get();

        return view('admin.products.index', compact('products', 'filterCategories'));
    }

    public function create()
    {
        $categories = Category::orderBy('sort_order')->get();

        return view('admin.products.create', compact('categories'));
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['track_stock'] = $request->boolean('track_stock');

        $product = Product::create($data);

        $this->syncGallery($request, $product);

        return redirect()->route('admin.products.index')->with('success', 'Product created.');
    }

    public function edit(Product $product)
    {
        $categories = Category::orderBy('sort_order')->get();

        return view('admin.products.edit', compact('product', 'categories'));
    }

    public function update(Request $request, Product $product)
    {
        $data = $this->validated($request, $product->id);

        if ($request->hasFile('image')) {
            if ($product->image) {
                Storage::disk('public')->delete($product->image);
            }
            $data['image'] = $request->file('image')->store('products', 'public');
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['is_featured'] = $request->boolean('is_featured');
        $data['track_stock'] = $request->boolean('track_stock');

        $product->update($data);

        $this->syncGallery($request, $product);

        return redirect()->route('admin.products.index')->with('success', 'Product updated.');
    }

    public function destroy(Product $product)
    {
        if ($product->image) {
            Storage::disk('public')->delete($product->image);
        }

        foreach ($product->images as $image) {
            Storage::disk('public')->delete($image->image);
        }

        $product->delete();

        return back()->with('success', 'Product deleted.');
    }

    private function validated(Request $request, ?int $ignoreId = null): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:products,slug' . ($ignoreId ? ',' . $ignoreId : '')],
            'short_description' => ['nullable', 'string', 'max:500'],
            'description' => ['nullable', 'string'],
            'type' => ['required', 'in:ready,custom,service'],
            'fabric' => ['nullable', 'string', 'max:255'],
            'sizes' => ['nullable', 'string', 'max:255'],
            'price' => ['required', 'numeric', 'min:0'],
            'sale_price' => ['nullable', 'numeric', 'min:0', 'lt:price'],
            'stock' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'max:4096'],
            'gallery' => ['nullable', 'array'],
            'gallery.*' => ['image', 'max:4096'],
            'name_translations' => ['nullable', 'array'],
            'name_translations.ps' => ['nullable', 'string', 'max:255'],
            'name_translations.fa' => ['nullable', 'string', 'max:255'],
            'short_description_translations' => ['nullable', 'array'],
            'short_description_translations.ps' => ['nullable', 'string', 'max:1000'],
            'short_description_translations.fa' => ['nullable', 'string', 'max:1000'],
            'description_translations' => ['nullable', 'array'],
            'description_translations.ps' => ['nullable', 'string', 'max:20000'],
            'description_translations.fa' => ['nullable', 'string', 'max:20000'],
        ]);

        $sizes = array_values(array_filter(array_map('trim', explode(',', (string) ($data['sizes'] ?? '')))));
        $data['sizes'] = !empty($sizes) ? $sizes : null;
        $data['stock'] = $data['stock'] ?? 0;

        foreach (['name_translations', 'short_description_translations', 'description_translations'] as $key) {
            if (array_key_exists($key, $data)) {
                $data[$key] = $this->cleanTranslations($data[$key]);
            }
        }

        return $data;
    }

    private function cleanTranslations(?array $translations): ?array
    {
        $cleaned = [];

        foreach (['ps', 'fa'] as $locale) {
            $value = $translations[$locale] ?? null;
            $cleaned[$locale] = is_string($value) && trim($value) !== '' ? trim($value) : null;
        }

        $cleaned = array_filter($cleaned, fn ($value) => $value !== null);

        return !empty($cleaned) ? $cleaned : null;
    }

    private function syncGallery(Request $request, Product $product): void
    {
        if (!$request->hasFile('gallery')) {
            return;
        }

        foreach ($request->file('gallery') as $file) {
            $product->images()->create([
                'image' => $file->store('products', 'public'),
                'sort_order' => $product->images()->max('sort_order') + 1,
            ]);
        }
    }
}
