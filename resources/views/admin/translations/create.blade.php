@extends('layouts.admin')

@section('title', 'Add Translation')

@section('content')
    <div class="max-w-3xl">
        @if ($errors->any())
            <div class="mb-6 rounded-xl bg-red-50 px-4 py-3 text-sm font-medium text-red-700">
                <p class="font-semibold">Please fix the following errors:</p>
                <ul class="mt-1 list-inside list-disc">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <div class="rounded-2xl border border-brand-100 bg-white p-6 shadow-sm sm:p-8">
            <form method="POST" action="{{ route('admin.translations.store') }}">
                @csrf
                <div class="grid gap-5 sm:grid-cols-2">
                    <div>
                        <label for="group" class="mb-1.5 block text-sm font-medium text-brand-900">Group <span class="text-xs font-normal text-brand-400">(defaults to "general")</span></label>
                        <input type="text" id="group" name="group" value="{{ old('group') }}" list="groupSuggestions" placeholder="e.g. home"
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('group') border-red-400 @enderror">
                        <datalist id="groupSuggestions">
                            @foreach ($groups as $groupOption)
                                <option value="{{ $groupOption }}"></option>
                            @endforeach
                        </datalist>
                        @error('group')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="key" class="mb-1.5 block text-sm font-medium text-brand-900">Key <span class="text-red-500">*</span> <span class="text-xs font-normal text-brand-400">(lowercase, dots allowed, e.g. home.hero_title)</span></label>
                        <input type="text" id="key" name="key" value="{{ old('key') }}" required placeholder="home.hero_title"
                               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm font-mono outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('key') border-red-400 @enderror">
                        @error('key')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-6 space-y-5">
                    <div class="flex items-center justify-between">
                        <h3 class="text-sm font-bold uppercase tracking-wide text-brand-400">Translations</h3>
                        <p class="text-xs text-brand-400">Pashto/Farsi left empty fall back to English.</p>
                    </div>
                    <div>
                        <label for="en" class="mb-1.5 block text-sm font-medium text-brand-900">English</label>
                        <textarea id="en" name="en" rows="5" placeholder="Fallback language — shown when a translation is empty"
                                  class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('en') border-red-400 @enderror">{{ old('en') }}</textarea>
                        @error('en')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="ps" class="mb-1.5 block text-sm font-medium text-brand-900">Pashto</label>
                        <textarea id="ps" name="ps" rows="5" dir="rtl" placeholder="پښتو"
                                  class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('ps') border-red-400 @enderror">{{ old('ps') }}</textarea>
                        @error('ps')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label for="fa" class="mb-1.5 block text-sm font-medium text-brand-900">Farsi</label>
                        <textarea id="fa" name="fa" rows="5" dir="rtl" placeholder="فارسی"
                                  class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('fa') border-red-400 @enderror">{{ old('fa') }}</textarea>
                        @error('fa')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>

                <div class="mt-6 flex gap-3">
                    <button type="submit" class="rounded-xl bg-teal-700 px-6 py-3 text-sm font-semibold text-white transition hover:bg-teal-800">Create Translation</button>
                    <a href="{{ route('admin.translations.index') }}" class="rounded-xl border border-brand-200 px-6 py-3 text-sm font-semibold text-brand-700 transition hover:bg-brand-50">Cancel</a>
                </div>
            </form>
        </div>
    </div>
@endsection
