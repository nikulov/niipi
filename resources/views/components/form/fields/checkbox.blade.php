@props([
    'field',
])

<label class="inline-flex items-center gap-2">
    <input type="checkbox" wire:model.defer="{{ $field['wireModel'] }}" value="1" class="text-primary border-primary h-4 w-4 rounded" />

    <span class="text-sm text-gray-600">
        {{ $field['label'] }}
    </span>
</label>

@error($field['errorKey'])
    <p class="text-sm text-red-800">{{ $message }}</p>
@enderror
