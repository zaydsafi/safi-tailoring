<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class CategoryController extends Controller
{
    public function index()
    {
        $categories = Category::withCount('products')->orderBy('sort_order')->paginate(15);

        return view('admin.categories.index', compact('categories'));
    }

    public function create()
    {
        return view('admin.categories.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug'],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'name_translations' => ['nullable', 'array'],
            'name_translations.ps' => ['nullable', 'string', 'max:255'],
            'name_translations.fa' => ['nullable', 'string', 'max:255'],
            'description_translations' => ['nullable', 'array'],
            'description_translations.ps' => ['nullable', 'string', 'max:20000'],
            'description_translations.fa' => ['nullable', 'string', 'max:20000'],
        ]);

        $data = $this->cleanTranslationData($data);

        if ($request->hasFile('image')) {
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        Category::create($data);

        return redirect()->route('admin.categories.index')->with('success', t('admin.flash.category_created', 'Category created.'));
    }

    public function edit(Category $category)
    {
        return view('admin.categories.edit', compact('category'));
    }

    public function update(Request $request, Category $category)
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'slug' => ['nullable', 'string', 'max:255', 'unique:categories,slug,' . $category->id],
            'description' => ['nullable', 'string', 'max:1000'],
            'image' => ['nullable', 'image', 'max:2048'],
            'is_active' => ['nullable', 'boolean'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'name_translations' => ['nullable', 'array'],
            'name_translations.ps' => ['nullable', 'string', 'max:255'],
            'name_translations.fa' => ['nullable', 'string', 'max:255'],
            'description_translations' => ['nullable', 'array'],
            'description_translations.ps' => ['nullable', 'string', 'max:20000'],
            'description_translations.fa' => ['nullable', 'string', 'max:20000'],
        ]);

        $data = $this->cleanTranslationData($data);

        if ($request->hasFile('image')) {
            if ($category->image) {
                Storage::disk('public')->delete($category->image);
            }
            $data['image'] = $request->file('image')->store('categories', 'public');
        }

        $data['is_active'] = $request->boolean('is_active');
        $data['sort_order'] = $data['sort_order'] ?? 0;

        $category->update($data);

        return redirect()->route('admin.categories.index')->with('success', t('admin.flash.category_updated', 'Category updated.'));
    }

    public function destroy(Category $category)
    {
        if ($category->products()->exists()) {
            return back()->with('error', t('admin.flash.category_has_products', 'This category has products. Move or delete them first.'));
        }

        if ($category->image) {
            Storage::disk('public')->delete($category->image);
        }

        $category->delete();

        return back()->with('success', t('admin.flash.category_deleted', 'Category deleted.'));
    }

    private function cleanTranslationData(array $data): array
    {
        foreach (['name_translations', 'description_translations'] as $key) {
            if (!array_key_exists($key, $data)) {
                continue;
            }

            $translations = $data[$key] ?? [];
            $cleaned = [];

            foreach (['ps', 'fa'] as $locale) {
                $value = $translations[$locale] ?? null;
                $cleaned[$locale] = is_string($value) && trim($value) !== '' ? trim($value) : null;
            }

            $cleaned = array_filter($cleaned, fn ($value) => $value !== null);

            $data[$key] = !empty($cleaned) ? $cleaned : null;
        }

        return $data;
    }
}
