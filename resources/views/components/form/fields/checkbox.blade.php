@props([
    'field',
])

<label class="inline-flex cursor-pointer items-center gap-2">
    <input
        type="checkbox"
        wire:model="{{ $field['wireModel'] }}"
        @if (!empty($field['required'] ?? null)) required aria-required="true" @endif
        class="peer sr-only"
    />

    <span
        class="border-primary dark:border-accent-dark peer-checked:bg-primary dark:peer-checked:bg-accent-dark flex h-4 min-h-4 w-4 min-w-4 items-center justify-center border transition-colors peer-checked:text-white"
    ></span>

    <span class="text-text dark:text-white-dark pl-5 text-sm">
        {!! $field['label'] !!}
    </span>
</label>

@error($field['errorKey'])
    <p class="text-sm text-[#ed6262]">{{ $message }}</p>
@enderror
