@php $product = $product ?? null; @endphp
<div class="grid gap-5 sm:grid-cols-2">
    <div>
        <label for="category_id" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.products.category', 'Category') }} <span class="text-red-500">*</span></label>
        <select id="category_id" name="category_id" required
                class="w-full rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('category_id') border-red-400 @enderror">
            <option value="">{{ t('admin.products.select_category', 'Select a category…') }}</option>
            @foreach ($categories as $category)
                <option value="{{ $category->id }}" @selected((string) old('category_id', $product->category_id ?? '') === (string) $category->id)>{{ $category->name }}</option>
            @endforeach
        </select>
        @error('category_id')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="type" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.products.product_type', 'Product Type') }} <span class="text-red-500">*</span></label>
        <select id="type" name="type" required
                class="w-full rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('type') border-red-400 @enderror">
            @foreach (['ready' => t('admin.products.type_ready', 'Ready Made'), 'custom' => t('admin.products.type_custom', 'Custom Tailoring'), 'service' => t('admin.products.type_service', 'Service / Repair')] as $value => $label)
                <option value="{{ $value }}" @selected(old('type', $product->type ?? 'ready') === $value)>{{ $label }}</option>
            @endforeach
        </select>
        @error('type')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="name" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.common.name', 'Name') }} <span class="text-red-500">*</span></label>
        <input type="text" id="name" name="name" value="{{ old('name', isset($product) ? $product->getRawOriginal('name') : '') }}" required
               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('name') border-red-400 @enderror">
        @error('name')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="slug" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.products.slug', 'Slug') }} <span class="text-xs font-normal text-brand-400">{{ t('admin.products.slug_hint', '(leave blank to auto-generate)') }}</span></label>
        <input type="text" id="slug" name="slug" value="{{ old('slug', $product->slug ?? '') }}"
               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('slug') border-red-400 @enderror">
        @error('slug')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="short_description" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.products.short_description', 'Short Description') }}</label>
        <input type="text" id="short_description" name="short_description" value="{{ old('short_description', isset($product) ? $product->getRawOriginal('short_description') : '') }}" maxlength="500"
               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('short_description') border-red-400 @enderror">
        @error('short_description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="sm:col-span-2">
        <label for="description" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.products.full_description', 'Full Description') }}</label>
        <textarea id="description" name="description" rows="4"
                  class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('description') border-red-400 @enderror">{{ old('description', isset($product) ? $product->getRawOriginal('description') : '') }}</textarea>
        @error('description')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="rounded-2xl border border-dashed border-brand-200 bg-brand-50/40 p-4 sm:col-span-2 sm:p-5">
        <p class="mb-4 text-sm font-semibold text-brand-900">{{ t('admin.products.translations', 'Translations (Pashto / Farsi)') }} <span class="text-xs font-normal text-brand-400">{{ t('admin.products.translations_hint', '— optional; the English text above is shown when a translation is empty') }}</span></p>
        <div class="grid gap-5 sm:grid-cols-2">
            <div>
                <label for="name_ps" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.products.name_ps', 'Name (Pashto)') }}</label>
                <input type="text" id="name_ps" name="name_translations[ps]" lang="ps" dir="rtl" maxlength="255"
                       value="{{ old('name_translations.ps', data_get($product, 'name_translations.ps')) }}"
                       class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('name_translations.ps') border-red-400 @enderror">
                @error('name_translations.ps')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="name_fa" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.products.name_fa', 'Name (Farsi)') }}</label>
                <input type="text" id="name_fa" name="name_translations[fa]" lang="fa" dir="rtl" maxlength="255"
                       value="{{ old('name_translations.fa', data_get($product, 'name_translations.fa')) }}"
                       class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('name_translations.fa') border-red-400 @enderror">
                @error('name_translations.fa')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="short_description_ps" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.products.short_description_ps', 'Short Description (Pashto)') }}</label>
                <textarea id="short_description_ps" name="short_description_translations[ps]" lang="ps" dir="rtl" rows="2" maxlength="1000"
                          class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('short_description_translations.ps') border-red-400 @enderror">{{ old('short_description_translations.ps', data_get($product, 'short_description_translations.ps')) }}</textarea>
                @error('short_description_translations.ps')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div>
                <label for="short_description_fa" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.products.short_description_fa', 'Short Description (Farsi)') }}</label>
                <textarea id="short_description_fa" name="short_description_translations[fa]" lang="fa" dir="rtl" rows="2" maxlength="1000"
                          class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('short_description_translations.fa') border-red-400 @enderror">{{ old('short_description_translations.fa', data_get($product, 'short_description_translations.fa')) }}</textarea>
                @error('short_description_translations.fa')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="description_ps" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.products.full_description_ps', 'Full Description (Pashto)') }}</label>
                <textarea id="description_ps" name="description_translations[ps]" lang="ps" dir="rtl" rows="4"
                          class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('description_translations.ps') border-red-400 @enderror">{{ old('description_translations.ps', data_get($product, 'description_translations.ps')) }}</textarea>
                @error('description_translations.ps')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
            <div class="sm:col-span-2">
                <label for="description_fa" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.products.full_description_fa', 'Full Description (Farsi)') }}</label>
                <textarea id="description_fa" name="description_translations[fa]" lang="fa" dir="rtl" rows="4"
                          class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('description_translations.fa') border-red-400 @enderror">{{ old('description_translations.fa', data_get($product, 'description_translations.fa')) }}</textarea>
                @error('description_translations.fa')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
            </div>
        </div>
    </div>
    <div>
        <label for="fabric" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.products.fabric', 'Fabric') }}</label>
        <input type="text" id="fabric" name="fabric" value="{{ old('fabric', $product->fabric ?? '') }}" placeholder="{{ t('admin.products.fabric_placeholder', 'e.g. Premium Cotton') }}"
               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('fabric') border-red-400 @enderror">
        @error('fabric')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="sizes" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.products.sizes', 'Sizes') }} <span class="text-xs font-normal text-brand-400">{{ t('admin.products.sizes_hint', '(comma separated)') }}</span></label>
        <input type="text" id="sizes" name="sizes" value="{{ old('sizes', isset($product) ? implode(', ', $product->sizes ?? []) : '') }}" placeholder="S, M, L, XL"
               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('sizes') border-red-400 @enderror">
        @error('sizes')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="price" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.products.price', 'Price') }} <span class="text-red-500">*</span></label>
        <input type="number" id="price" name="price" step="0.01" min="0" value="{{ old('price', $product->price ?? '') }}" required
               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('price') border-red-400 @enderror">
        @error('price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="sale_price" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.products.sale_price', 'Sale Price') }} <span class="text-xs font-normal text-brand-400">{{ t('admin.products.sale_price_hint', '(must be lower than price)') }}</span></label>
        <input type="number" id="sale_price" name="sale_price" step="0.01" min="0" value="{{ old('sale_price', $product->sale_price ?? '') }}"
               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('sale_price') border-red-400 @enderror">
        @error('sale_price')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="stock" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.products.stock_quantity', 'Stock Quantity') }}</label>
        <input type="number" id="stock" name="stock" min="0" value="{{ old('stock', $product->stock ?? 0) }}"
               class="w-full rounded-xl border border-brand-200 px-4 py-2.5 text-sm outline-none transition focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20 @error('stock') border-red-400 @enderror">
        @error('stock')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div class="flex items-end gap-6 pb-1">
        <label class="flex items-center gap-2 text-sm font-medium text-brand-900">
            <input type="hidden" name="track_stock" value="0">
            <input type="checkbox" name="track_stock" value="1" @checked(old('track_stock', $product->track_stock ?? true)) class="h-4 w-4 rounded border-brand-300 text-teal-700 focus:ring-teal-600/30">
            {{ t('admin.products.track_stock', 'Track stock') }}
        </label>
        <label class="flex items-center gap-2 text-sm font-medium text-brand-900">
            <input type="hidden" name="is_featured" value="0">
            <input type="checkbox" name="is_featured" value="1" @checked(old('is_featured', $product->is_featured ?? false)) class="h-4 w-4 rounded border-brand-300 text-teal-700 focus:ring-teal-600/30">
            {{ t('admin.products.featured', 'Featured') }}
        </label>
        <label class="flex items-center gap-2 text-sm font-medium text-brand-900">
            <input type="hidden" name="is_active" value="0">
            <input type="checkbox" name="is_active" value="1" @checked(old('is_active', $product->is_active ?? true)) class="h-4 w-4 rounded border-brand-300 text-teal-700 focus:ring-teal-600/30">
            {{ t('admin.products.active', 'Active') }}
        </label>
    </div>
    <div>
        <label for="image" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.products.main_image', 'Main Image') }} <span class="text-xs font-normal text-brand-400">{{ t('admin.products.main_image_size', '(max 4MB') }}{{ isset($product) && $product->image ? ' ' . t('admin.products.main_image_keep', '— leave blank to keep current') : '' }})</span></label>
        <input type="file" id="image" name="image" accept="image/*"
               class="w-full rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-brand-700 hover:file:bg-brand-100 @error('image') border-red-400 @enderror">
        @error('image')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
    <div>
        <label for="gallery" class="mb-1.5 block text-sm font-medium text-brand-900">{{ t('admin.products.gallery_images', 'Gallery Images') }} <span class="text-xs font-normal text-brand-400">{{ t('admin.products.gallery_images_hint', '(multiple, max 4MB each)') }}</span></label>
        <input type="file" id="gallery" name="gallery[]" accept="image/*" multiple
               class="w-full rounded-xl border border-brand-200 bg-white px-4 py-2.5 text-sm file:mr-4 file:rounded-lg file:border-0 file:bg-brand-50 file:px-3 file:py-1.5 file:text-xs file:font-semibold file:text-brand-700 hover:file:bg-brand-100 @error('gallery.*') border-red-400 @enderror">
        @error('gallery.*')<p class="mt-1 text-xs text-red-600">{{ $message }}</p>@enderror
    </div>
</div>
