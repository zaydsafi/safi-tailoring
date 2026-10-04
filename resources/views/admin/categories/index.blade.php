@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
    <div class="mb-6 flex items-center justify-between">
        <p class="text-sm text-brand-500">{{ $categories->total() }} categor{{ $categories->total() === 1 ? 'y' : 'ies' }}</p>
        <a href="{{ route('admin.categories.create') }}" class="rounded-xl bg-teal-700 px-4 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">+ Add Category</a>
    </div>

    <div class="overflow-x-auto rounded-2xl border border-brand-100 bg-white shadow-sm">
        <table class="w-full min-w-[640px] text-sm">
            <thead>
                <tr class="border-b border-brand-100 bg-brand-50 text-left text-xs font-semibold uppercase tracking-wide text-brand-500">
                    <th class="px-4 py-3">Category</th>
                    <th class="px-4 py-3">Products</th>
                    <th class="px-4 py-3">Sort</th>
                    <th class="px-4 py-3">Status</th>
                    <th class="px-4 py-3 text-right">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-brand-50">
                @forelse ($categories as $category)
                    <tr class="transition hover:bg-brand-50/50">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if ($category->image)
                                    <img src="{{ Storage::url($category->image) }}" alt="" class="h-10 w-10 rounded-lg object-cover">
                                @endif
                                <div>
                                    <p class="font-semibold text-brand-900">{{ $category->name }}</p>
                                    <p class="text-xs text-brand-400">/{{ $category->slug }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3">{{ $category->products_count }}</td>
                        <td class="px-4 py-3">{{ $category->sort_order }}</td>
                        <td class="px-4 py-3">
                            <span class="rounded-full px-2.5 py-1 text-xs font-semibold {{ $category->is_active ? 'bg-green-100 text-green-800' : 'bg-gray-100 text-gray-500' }}">
                                {{ $category->is_active ? 'Active' : 'Hidden' }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('admin.categories.edit', $category) }}" class="rounded-lg border border-brand-200 px-3 py-1.5 text-xs font-semibold text-brand-700 transition hover:bg-brand-50">Edit</a>
                                <form method="POST" action="{{ route('admin.categories.destroy', $category) }}" onsubmit="return confirm('Delete category &quot;{{ $category->name }}&quot;?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="rounded-lg border border-red-200 px-3 py-1.5 text-xs font-semibold text-red-600 transition hover:bg-red-50">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="5" class="px-4 py-10 text-center text-brand-400">No categories yet. Create your first one.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-6">{{ $categories->links() }}</div>
@endsection
