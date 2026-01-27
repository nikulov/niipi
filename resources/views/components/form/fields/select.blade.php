@props([
    'field',
])

<x-form.label required="{{$field['required']}}">
    {{ $field['label'] }}
</x-form.label>

<select
    wire:model.defer="{{ $field['wireModel'] }}"
    class="border-b-primary focus:border-accent w-full border-b px-3 py-2 text-sm focus:outline-none"
>
    <option value="">{{ __('page.select_options') }}</option>

    @foreach ($field['options'] ?? [] as $opt)
        <option value="{{ $opt['value'] }}">
            {{ $opt['label'] }}
        </option>
    @endforeach
</select>

@error($field['errorKey'])
    <p class="text-sm text-red-800">{{ $message }}</p>
@enderror
