@php
    $item = $item ?? ['description' => '', 'measurements' => [], 'price' => '', 'quantity' => 1];
    $measurementFields = $measurementFields ?? [];
    $savedProfile = $savedProfile ?? null;
@endphp

<div class="rounded-lg border border-brand-100 p-4" data-item-index="{{ $index }}">
    <div class="flex items-center justify-between gap-2 mb-3">
        <span class="text-sm font-semibold text-brand-900">{{ t('admin.custom_orders.item', 'Item') }} #{{ $index + 1 }}</span>
        <button type="button" class="text-xs text-red-600 hover:underline remove-item-btn" data-index="{{ $index }}">{{ t('admin.common.remove', 'Remove') }}</button>
    </div>
    <div class="grid gap-3 sm:grid-cols-2">
        <div>
            <label for="items-{{ $index }}-description" class="mb-1 block text-xs font-medium text-brand-600">{{ t('admin.custom_orders.description', 'What to make') }} <span class="text-red-500">*</span></label>
            <input type="text" id="items-{{ $index }}-description" name="items[{{ $index }}][description]" value="{{ $item['description'] ?? '' }}" required placeholder="e.g. 2-piece suit"
                   class="w-full rounded-lg border border-brand-200 px-3 py-2 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
        </div>
        <div>
            <label for="items-{{ $index }}-price" class="mb-1 block text-xs font-medium text-brand-600">{{ t('admin.custom_orders.price', 'Price (AFN)') }} <span class="text-red-500">*</span></label>
            <input type="number" id="items-{{ $index }}-price" step="0.01" min="0" name="items[{{ $index }}][price]" value="{{ $item['price'] ?? '' }}" required
                   class="w-full rounded-lg border border-brand-200 px-3 py-2 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
        </div>
        <div>
            <label for="items-{{ $index }}-quantity" class="mb-1 block text-xs font-medium text-brand-600">{{ t('admin.common.quantity', 'Quantity') }} <span class="text-red-500">*</span></label>
            <input type="number" id="items-{{ $index }}-quantity" min="1" name="items[{{ $index }}][quantity]" value="{{ $item['quantity'] ?? 1 }}" required
                   class="w-full rounded-lg border border-brand-200 px-3 py-2 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
        </div>
        <div>
            <label for="items-{{ $index }}-fabric" class="mb-1 block text-xs font-medium text-brand-600">{{ t('admin.custom_orders.fabric', 'Fabric (optional)') }}</label>
            <input type="text" id="items-{{ $index }}-fabric" name="items[{{ $index }}][fabric]" value="{{ old('items.' . $index . '.fabric') }}" placeholder="Cotton, linen, silk…"
                   class="w-full rounded-lg border border-brand-200 px-3 py-2 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
        </div>
    </div>

    <div class="mt-4 border-t border-brand-100 pt-3">
        <div class="flex items-center justify-between mb-2">
            <span class="text-xs font-medium text-brand-600">{{ t('admin.custom_orders.measurements_optional', 'Measurements (inches, optional)') }}</span>
            @if ($savedProfile)
                <button type="button" class="text-xs text-teal-700 hover:underline apply-profile-btn" data-index="{{ $index }}">
                    {{ t('admin.custom_orders.apply_saved', 'Apply saved measurements') }}
                </button>
            @endif
        </div>
        <div class="grid gap-2 sm:grid-cols-3">
            @foreach ($measurementFields as $field => $label)
                <div>
                    <label for="items-{{ $index }}-measurements-{{ $field }}" class="block text-[11px] font-medium text-brand-500 mb-0.5">{{ $label }}</label>
                    <input type="number" id="items-{{ $index }}-measurements-{{ $field }}" step="0.5" min="0" name="items[{{ $index }}][measurements][{{ $field }}]"
                           value="{{ old('items.' . $index . '.measurements.' . $field, ($item['measurements'] ?? [])[$field] ?? $savedProfile?->$field ?? '') }}"
                           class="w-full rounded-lg border border-brand-200 px-2 py-1.5 text-sm focus:border-teal-600 focus:ring-2 focus:ring-teal-600/20">
                </div>
            @endforeach
        </div>
    </div>
</div>
