@props([
    'field',
])

@php
    $cfg = is_array($field['file'] ?? null) ? $field['file'] : [];
    $multiple = (bool) ($cfg['multiple'] ?? false);
    $accept = $cfg['acceptAttr'] ?? null;
@endphp

<div
    x-data="{
        files: [],
        updateFiles(event) {
            this.files = Array.from(event.target.files || [])
        },
    }"
    class="flex flex-col gap-2"
>
    <x-form.label required="{{$field['required']}}">
        {{ $field['label'] }}
    </x-form.label>

    <div class="flex flex-wrap items-center gap-3">
        <label class="cursor-pointer">
            <div class="text-text min-w-34 border border-gray-300 bg-gray-200 px-3 py-2 select-none">
                {{ __('page.choose_file') }}
            </div>

            <input
                type="file"
                wire:model="{{ $field['wireModel'] }}"
                @if ($multiple) multiple @endif
                @if (is_string($accept) && $accept !== '') accept="{{ $accept }}" @endif
                @if (!empty($field['required'] ?? null)) required aria-required="true" @endif
                class="hidden"
                @change="updateFiles($event)"
            />
        </label>

        <span class="text-text dark:text-white-dark text-sm" x-show="files.length === 0">
            {{ __('page.no_file') }}
        </span>

        <span
            x-show="files.length > 0"
            x-cloak
            class="text-text dark:text-white-dark text-sm"
            x-text="files.map((file) => file.name).join(', ')"
        ></span>
    </div>
</div>

@error($field['errorKey'])
    <p class="text-sm text-red-800 dark:text-red-400">{{ $message }}</p>
@enderror
