@extends('layouts.admin')

@section('title', t('admin.products.edit_title', 'Edit Product'))

@section('content')
    <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm sm:p-8">
        @if ($product->image || $product->images->count())
            <div class="mb-6">
                <p class="mb-2 text-xs font-semibold uppercase tracking-wide text-brand-400">{{ t('admin.products.current_images', 'Current Images') }}</p>
                <div class="flex flex-wrap gap-3">
                    @if ($product->image)
                        <div>
                            <img src="{{ Storage::url($product->image) }}" alt="{{ t('admin.products.main', 'Main') }}" class="h-24 w-24 rounded-xl border-2 border-teal-600 object-cover">
                            <p class="mt-1 text-center text-[11px] font-medium text-brand-500">{{ t('admin.products.main', 'Main') }}</p>
                        </div>
                    @endif
                    @foreach ($product->images as $image)
                        <img src="{{ Storage::url($image->image) }}" alt="" class="h-24 w-24 rounded-xl object-cover">
                    @endforeach
                </div>
            </div>
        @endif
        <form method="POST" action="{{ route('admin.products.update', $product) }}" enctype="multipart/form-data">
            @csrf
            @method('PUT')
            @include('admin.products._form')
            <div class="mt-6 flex gap-3">
                <button type="submit" class="rounded-xl bg-teal-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-teal-800">{{ t('admin.common.save_changes', 'Save Changes') }}</button>
                <a href="{{ route('admin.products.index') }}" class="rounded-xl border border-brand-200 px-6 py-3 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">{{ t('admin.common.cancel', 'Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
