@props([
    'field',
])

<x-form.label required="{{$field['required']}}">
    {{ $field['label'] }}
</x-form.label>

<textarea
    wire:model.defer="{{ $field['wireModel'] }}"
    rows="{{ $field['rows'] ?? 4 }}"
    class="border-b-primary focus:border-accent w-full border-b px-3 py-2 text-sm focus:outline-none"
></textarea>

@error($field['errorKey'])
    <p class="text-sm text-red-800">{{ $message }}</p>
@enderror
