@props([
    'field',
])

<x-form.label required="{{$field['required']}}">
    {{ $field['label'] }}
</x-form.label>

<textarea
    x-data="{
        resize() {
            const el = this.$refs.ta
            if (! el) return
            el.style.height = 'auto'
            el.style.height = el.scrollHeight + 'px'
        },
    }"
    x-ref="ta"
    x-init="$nextTick(() => resize())"
    @input="resize()"
    wire:model="{{ $field['wireModel'] }}"
    @if(!empty($field['placeholder'] ?? null)) placeholder="{{ $field['placeholder'] }}" @endif
    @if (!empty($field['required'] ?? null)) required aria-required="true" @endif
    rows="1"
    class="border-b-primary placeholder dark:border-b-accent-dark dark:focus:border-b-accent-add-dark focus:border-b-accent text-text dark:text-white-dark min-h-9 w-full resize-none overflow-hidden border-b px-3 py-2 text-sm focus:outline-none"
></textarea>

@error($field['errorKey'])
    <p class="text-sm text-red-800">{{ $message }}</p>
@enderror
