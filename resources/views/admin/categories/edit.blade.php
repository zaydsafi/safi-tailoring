@extends('layouts.admin')

@section('title', 'Edit Category')

@section('content')
    <div class="max-w-2xl rounded-2xl border border-brand-100 bg-white p-6 shadow-sm sm:p-8">
        @if ($category->image)
            <div class="mb-6">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-brand-400">Current Image</p>
                <img src="{{ Storage::url($category->image) }}" alt="" class="h-24 w-24 rounded-xl object-cover">
            </div>
        @endif
        <form method="POST" action="{{ route('admin.categories.update', $category) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.categories._form')
            <div class="mt-6 flex gap-3">
                <button type="submit" class="rounded-xl bg-teal-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-teal-800">Save Changes</button>
                <a href="{{ route('admin.categories.index') }}" class="rounded-xl border border-brand-200 px-6 py-3 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">Cancel</a>
            </div>
        </form>
    </div>
@endsection
