@extends('layouts.admin')

@section('title', 'Add Category')

@section('content')
    <div class="max-w-2xl rounded-2xl border border-brand-100 bg-white p-6 shadow-sm sm:p-8">
        <form method="POST" action="{{ route('admin.categories.store') }}" enctype="multipart/form-data">
            @csrf
            @include('admin.categories._form')
            <div class="mt-6 flex gap-3">
                <button type="submit" class="rounded-xl bg-teal-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-teal-800">Create Category</button>
                <a href="{{ route('admin.categories.index') }}" class="rounded-xl border border-brand-200 px-6 py-3 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">Cancel</a>
            </div>
        </form>
    </div>
@endsection
