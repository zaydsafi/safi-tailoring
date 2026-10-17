@extends('layouts.admin')

@section('title', t('admin.roles.add', 'Add Role'))

@section('content')
    <div class="mb-6">
        <a href="{{ route('admin.roles.index') }}" class="text-sm font-semibold text-brand-500 hover:text-brand-700">{{ t('admin.roles.back_to_roles', '← Back to roles') }}</a>
    </div>

    <div class="max-w-3xl rounded-2xl border border-brand-100 bg-white p-6 shadow-sm sm:p-8">
        <h2 class="mb-6 text-lg font-bold text-brand-900">{{ t('admin.roles.new_role_title', 'Create role') }}</h2>

        <form method="POST" action="{{ route('admin.roles.store') }}">
            @csrf
            @include('admin.roles._form')
            <div class="mt-8 flex justify-end gap-3 border-t border-brand-100 pt-5">
                <a href="{{ route('admin.roles.index') }}" class="rounded-xl border border-brand-200 px-4 py-2.5 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">{{ t('admin.common.cancel', 'Cancel') }}</a>
                <button type="submit" class="rounded-xl bg-teal-700 px-5 py-2.5 text-sm font-semibold text-white transition hover:bg-teal-800">{{ t('admin.roles.create_role', 'Create role') }}</button>
            </div>
        </form>
    </div>
@endsection
