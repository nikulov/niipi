@props([
    'items',
    'active' => null,
])

<div {{ $attributes->merge(['class' => 'mb-12 md:hidden']) }}>
    <select
            class="w-full border border-border focus:border-accent focus-visible:outline-0 bg-white px-4 py-3 uppercase text-primary"
            wire:change="setCategory($event.target.value || null)"
    >
        @foreach($items as $item)
            <option
                    value="{{ $item['slug'] }}"
                    @selected($active === $item['slug'])
            >
                {{ $item['name'] }} [{{ $item['count'] }}]
            </option>
        @endforeach
    </select>
</div>