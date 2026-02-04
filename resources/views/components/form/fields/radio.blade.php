@props([
    'field',
])

<div class="space-y-2">
    <x-form.label required="{{$field['required']}}">
        {{ $field['label'] }}
    </x-form.label>

    @foreach ($field['options'] ?? [] as $opt)
        <label class="inline-flex cursor-pointer items-center gap-2">
            <input
                type="radio"
                wire:model="{{ $field['wireModel'] }}"
                value="{{ $opt['value'] ?? '' }}"
                @if (! empty($field["required"] ?? null) && $loop->first) required aria-required="true" @endif
                class="peer sr-only"
            />

            <span
                class="border-primary dark:border-accent-dark peer-checked:border-primary peer-checked:dark:border-accent-dark flex h-4 w-4 items-center justify-center rounded-full border transition-colors peer-checked:[&>span]:scale-100"
            >
                <span class="bg-primary dark:bg-accent-dark h-2 w-2 scale-0 rounded-full transition-transform"></span>
            </span>

            <span class="text-text dark:text-white-dark pr-4 text-sm">
                {{ $opt['label'] ?? '' }}
            </span>
        </label>
    @endforeach
</div>

@error($field['errorKey'])
    <p class="text-sm text-[#ed6262]">{{ $message }}</p>
@enderror
