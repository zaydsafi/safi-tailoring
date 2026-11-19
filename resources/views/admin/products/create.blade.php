@extends('layouts.admin')

@section('title', t('admin.products.create_title', 'Add Product'))

@section('content')
    <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm sm:p-8">
        <form method="POST" action="{{ route('admin.products.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.products._form')
            <div class="mt-6 flex gap-3">
                <button type="submit" class="rounded-xl bg-gold-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-gold-800">{{ t('admin.products.create', 'Create Product') }}</button>
                <a href="{{ route('admin.products.index') }}" class="rounded-xl border border-brand-200 px-6 py-3 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">{{ t('admin.common.cancel', 'Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
