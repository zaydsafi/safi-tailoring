@extends('layouts.admin')

@section('title', 'Edit User')

@section('content')
    <div class="max-w-2xl rounded-2xl border border-brand-100 bg-white p-5 shadow-sm sm:p-8">
        <form method="POST" action="{{ route('admin.users.update', $user) }}">
            @csrf
            @method('PUT')
            @include('admin.users._form')
            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="rounded-xl bg-teal-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-teal-800">Save Changes</button>
                <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-brand-200 px-6 py-3 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">Cancel</a>
            </div>
        </form>
    </div>
@endsection
