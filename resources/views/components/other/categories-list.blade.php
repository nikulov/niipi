@props([
    'items',
    'active' => null,
])

<div {{ $attributes->merge(['class' => 'relative font-century hidden md:flex flex-wrap justify-center gap-8 mb-20']) }}>
    @foreach ($items as $item)
        <button
            type="button"
            wire:click="setCategory({{ $item['slug'] ? "'{$item['slug']}'" : 'null' }})"
            class="{{ $active === $item['slug'] ? 'text-accent [text-shadow:0.03em_0_currentColor,-0.03em_0_currentColor] dark:text-[#56bcc3]' : 'text-[#80909d] dark:text-[#839c9d]' }} relative cursor-pointer transition-colors duration-250 hover:text-[#5B8EAE] hover:[text-shadow:0.02em_0_currentColor,-0.02em_0_currentColor]"
        >
            {{ $item['name'] }}
            <span class="opacity-60">[{{ $item['count'] }}]</span>
        </button>
    @endforeach
</div>
