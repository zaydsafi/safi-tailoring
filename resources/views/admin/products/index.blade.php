@extends('layouts.admin')

@section('title', 'Products')

@section('content')
    <div class="mb-6 flex flex-wrap items-center justify-between gap-3">
        <form method="GET" class="flex flex-wrap items-center gap-2">
            <input type="text" name="q" value="{{ request('q') }}" placeholder="Search products…"
                   class="w-56 rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
            <select name="category" class="rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                <option value="">All categories</option>
                @foreach ($filterCategories as $category)
                    <option value="{{ $category->id }}" @selected(request('category') == $category->id)>{{ $category->name }}</option>
                @endforeach
            </select>
            <button type="submit" class="rounded-xl bg-brand-900 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-brand-800">Filter</button>
            @if (request()->filled('q') || request()->filled('category'))
                <a href="{{ route('admin.products.index') }}" class="text-sm font-semibold text-brand-500 hover:text-brand-700">Clear</a>
            @endif
        </form>
        <a href="{{ route('admin.products.create') }}" class="rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">+ Add Product</a>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-brand-100 bg-white shadow-sm">
        <table class="w-full min-w-[840px] text-sm">
            <thead>
                <tr class="border-b border-brand-100 bg-brand-50 text-left text-xs font-semibold uppercase tracking-wide text-brand-500">
                    <th class="px-4 py-3">Product</th>
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Type</th>
                    <th class="px-4 py-3">Price</th>
                    <th class="px-4 py-3">Stock</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse ($products as $product)
                    <tr class="transition hover:bg-brand-50/50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if ($product->image)
                                    <img src="{{ Storage::url($product->image) }}" alt="" class="h-10 w-10 rounded-lg object-cover">
                                @endif
                                <div>
                                    <p class="font-semibold text-brand-900">{{ $product->name }}</p>
                                    @if ($product->is_featured)
                                        <span class="text-[11px] font-semibold text-gold-600">Featured</span>
                                    @endif
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ $product->category->name ?? '—' }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ ['ready' => 'bg-blue-50 text-blue-700', 'custom' => 'bg-purple-50 text-purple-700', 'service' => 'bg-gray-100 text-gray-600'][$product->type] ?? 'bg-gray-100 text-gray-600' }}">
                                {{ ucfirst($product->type) }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="font-semibold">{{ money($product->effectivePrice()) }}</span>
                            @if ($product->onSale())
                                <span class="ml-1 text-xs text-brand-400 line-through">{{ money($product->price, false) }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            @if ($product->track_stock)
                                <span class="font-semibold {{ $product->stock === 0 ? 'text-red-600' : ($product->stock <= 5 ? 'text-yellow-600' : 'text-brand-900') }}">{{ $product->stock }}</span>
                            @else
                                <span class="text-brand-400">—</span>
                            @endif
                        </td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $product->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}">
                                {{ $product->is_active ? 'Active' : 'Hidden' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.products.edit', $product) }}" class="rounded-lg border border-brand-200 px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:bg-brand-50">Edit</a>
                                <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Delete product &quot;{{ $product->name }}&quot;? This cannot be undone.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center text-brand-400">No products found.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $products->links() }}</div>
@endsection
