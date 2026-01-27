@props([
    'field',
])

@php
    $cfg = is_array($field['file'] ?? null) ? $field['file'] : [];
    $multiple = (bool) ($cfg['multiple'] ?? false);
    $accept = $cfg['acceptAttr'] ?? null;
@endphp

<input
    type="file"
    wire:model="{{ $field['wireModel'] }}"
    @if ($multiple) multiple @endif
    @if (is_string($accept) && $accept !== '') accept="{{ $accept }}" @endif
    class="block w-full text-sm text-gray-600 file:mr-4 file:rounded-md file:border-0 file:bg-gray-100 file:px-4 file:py-2 file:text-sm file:font-medium hover:file:bg-gray-200"
/>

@error($field['errorKey'])
    <p class="text-sm text-red-800">{{ $message }}</p>
@enderror
