@extends('layouts.admin')

@section('title', t('admin.users.create_title', 'Add User'))

@section('content')
    <div class="max-w-2xl rounded-2xl border border-brand-100 bg-white p-5 shadow-sm sm:p-8">
        <form method="POST" action="{{ route('admin.users.store') }}">
            @csrf
            @include('admin.users._form')
            <div class="mt-6 flex flex-wrap gap-3">
                <button type="submit" class="rounded-xl bg-teal-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-teal-800">{{ t('admin.users.create', 'Create User') }}</button>
                <a href="{{ route('admin.users.index') }}" class="rounded-xl border border-brand-200 px-6 py-3 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">{{ t('admin.common.cancel', 'Cancel') }}</a>
            </div>
        </form>
    </div>
@endsection
