@props([
    'field',
])

@php
    $isPhone = ($field['type'] ?? null) === 'phone';
@endphp

<x-form.label required="{{$field['required']}}">
    {{ $field['label'] }}
</x-form.label>

<input
    type="{{ $field['inputType'] ?? 'text' }}"
    wire:model="{{ $field['wireModel'] }}"
    @if ($isPhone)
        x-data="phoneMask('{{ $field['wireModel'] }}')"
        x-on:input="onInput($el)"
        x-on:focus="onFocus($el)"
        x-on:blur="onBlur($el)"
        x-on:keydown.backspace="onBackspace($event)"
        inputmode="tel"
        autocomplete="tel"
        maxlength="16"
    @endif
    @if(!empty($field['placeholder'] ?? null)) placeholder="{{ $field['placeholder'] }}" @endif
    @if (!empty($field['required'] ?? null)) required aria-required="true" @endif
    class="border-b-primary placeholder dark:border-b-accent-dark dark:focus:border-b-accent-add-dark focus:border-b-accent text-text dark:text-white-dark w-full border-b px-3 py-2 text-sm focus:outline-none"
/>

@error($field['errorKey'])
    <p class="text-sm text-[#ed6262]">{{ $message }}</p>
@enderror