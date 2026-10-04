@php $category = $category ?? null; @endphp
<div class="grid gap-5 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="name" class="mb-1.5 block text-sm font-medium text-brand-900">Name <span class="text-red-500">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', isset($category) ? $category->getRawOriginal('name') : '') }}" required
               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('name') border-red-400 @enderror">
        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="slug" class="mb-1.5 block text-sm font-medium text-brand-900">Slug <span class="text-xs font-normal text-brand-400">(leave blank to auto-generate)</span></label>
        <input type="text" id="slug" name="slug" value="{{ old('slug', $category->slug ?? '') }}"
               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('slug') border-red-400 @enderror">
        @error('slug')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="description" class="mb-1.5 block text-sm font-medium text-brand-900">Description</label>
        <textarea id="description" name="description" rows="3"
                  class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('description') border-red-400 @enderror">{{ old('description', isset($category) ? $category->getRawOriginal('description') : '') }}</textarea>
        @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="rounded-2xl border border-dashed border-brand-200 bg-brand-50/40 p-4 sm:col-span-2 sm:p-5">
        <p class="mb-4 text-sm font-semibold text-brand-900">Translations (Pashto / Farsi) <span class="text-xs font-normal text-brand-400">— optional; the English text above is shown when a translation is empty</span></p>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="name_ps" class="mb-1.5 block text-sm font-medium text-brand-900">Name (Pashto)</label>
                <input type="text" id="name_ps" name="name_translations[ps]" lang="ps" dir="rtl" maxlength="255"
                       value="{{ old('name_translations.ps', data_get($category, 'name_translations.ps')) }}"
                       class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('name_translations.ps') border-red-400 @enderror">
                @error('name_translations.ps')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="name_fa" class="mb-1.5 block text-sm font-medium text-brand-900">Name (Farsi)</label>
                <input type="text" id="name_fa" name="name_translations[fa]" lang="fa" dir="rtl" maxlength="255"
                       value="{{ old('name_translations.fa', data_get($category, 'name_translations.fa')) }}"
                       class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('name_translations.fa') border-red-400 @enderror">
                @error('name_translations.fa')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="description_ps" class="mb-1.5 block text-sm font-medium text-brand-900">Description (Pashto)</label>
                <textarea id="description_ps" name="description_translations[ps]" lang="ps" dir="rtl" rows="3"
                          class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('description_translations.ps') border-red-400 @enderror">{{ old('description_translations.ps', data_get($category, 'description_translations.ps')) }}</textarea>
                @error('description_translations.ps')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="description_fa" class="mb-1.5 block text-sm font-medium text-brand-900">Description (Farsi)</label>
                <textarea id="description_fa" name="description_translations[fa]" lang="fa" dir="rtl" rows="3"
                          class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('description_translations.fa') border-red-400 @enderror">{{ old('description_translations.fa', data_get($category, 'description_translations.fa')) }}</textarea>
                @error('description_translations.fa')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
    <div>
        <label for="sort_order" class="mb-1.5 block text-sm font-medium text-brand-900">Sort Order</label>
        <input type="number" id="sort_order" name="sort_order" min="0" value="{{ old('sort_order', $category->sort_order ?? 0) }}"
               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('sort_order') border-red-400 @enderror">
        @error('sort_order')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="flex items-end pb-1">
        <label class="flex items-center gap-2 text-sm font-medium text-brand-900">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $category->is_active ?? true)) class="h-4 w-4 rounded border-brand-300 text-teal-700 focus:ring-teal-600/30">
            Active (visible in storefront)
        </label>
    </div>
    <div class="sm:col-span-2">
        <label for="image" class="mb-1.5 block text-sm font-medium text-brand-900">Image <span class="text-xs font-normal text-brand-400">(max 2MB — leave blank to keep current)</span></label>
        <input type="file" id="image" name="image" accept="image/*"
               class="w-full rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-brand-700 hover:file:bg-brand-100 @error('image') border-red-400 @enderror">
        @error('image')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
