@props([
    'field',
])

<x-form.label required="{{$field['required']}}">
    {{ $field['label'] }}
</x-form.label>

<textarea
    wire:model="{{ $field['wireModel'] }}"
    rows="{{ $field['rows'] ?? 4 }}"
    @if(!empty($field['placeholder'] ?? null)) placeholder="{{ $field['placeholder'] }}" @endif
    @if (!empty($field['required'] ?? null)) required aria-required="true" @endif
    class="border-b-primary placeholder dark:border-b-accent-dark dark:focus:border-b-accent-add-dark focus:border-b-accent text-text dark:text-white-dark w-full border-b px-3 py-2 text-sm focus:outline-none"
></textarea>

@error($field['errorKey'])
    <p class="text-sm text-red-800">{{ $message }}</p>
@enderror
