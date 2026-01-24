@props([
    'items',
    'active' => null,
])

<div {{ $attributes->merge(['class' => 'mb-12 md:hidden']) }}>
    <select
        class="border-border focus:border-accent text-text font-century dark:bg-background-dark dark:text-text-dark w-full border bg-white px-4 py-3 uppercase focus-visible:outline-0"
        wire:change="setCategory($event.target.value || null)"
    >
        @foreach ($items as $item)
            <option value="{{ $item['slug'] }}" @selected($active === $item['slug'])>{{ $item['name'] }} [{{ $item['count'] }}]</option>
        @endforeach
    </select>
</div>
