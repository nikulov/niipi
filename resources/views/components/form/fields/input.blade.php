@props([
    'field',
])

<x-form.label required="{{$field['required']}}">
    {{ $field['label'] }}
</x-form.label>

<input
    type="{{ $field['inputType'] ?? 'text' }}"
    wire:model.defer="{{ $field['wireModel'] }}"
    class="border-b-primary focus:border-accent w-full border-b px-3 py-2 text-sm focus:outline-none"
/>

@error($field['errorKey'])
    <p class="text-sm text-red-800">{{ $message }}</p>
@enderror
