@props([
    'field',
])

<div class="space-y-2">
    @foreach ($field['options'] ?? [] as $opt)
        <label class="flex items-center gap-2">
            <input
                type="radio"
                wire:model.defer="{{ $field['wireModel'] }}"
                value="{{ $opt['value'] }}"
                class="text-primary border-primary h-4 w-4"
            />
            <span class="text-sm text-gray-600">{{ $opt['label'] }}</span>
        </label>
    @endforeach
</div>

@error($field['errorKey'])
    <p class="text-sm text-red-800">{{ $message }}</p>
@enderror
