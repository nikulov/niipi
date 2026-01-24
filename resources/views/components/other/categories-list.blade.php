@props([
    'items',
    'active' => null,
])

<div {{ $attributes->merge(['class' => 'relative font-century hidden md:flex flex-wrap justify-center gap-8 mb-20']) }}>
    @foreach ($items as $item)
        <button
            type="button"
            wire:click="setCategory({{ $item['slug'] ? "'{$item['slug']}'" : 'null' }})"
            class="{{ $item['slug'] ? 'pl-8 before:content-[""] transition-colors duration-100 before:absolute before:-left-px before:w-1 before:h-5 before:bg-[url("/resources/images/layout/acc-dots-dark.svg")]' : '' }} {{ $active === $item['slug'] ? 'text-accent dark:text-accent-add-dark [text-shadow:0.04em_0_currentColor,-0.04em_0_currentColor]' : 'text-primary dark:text-accent-add-dark' }} relative cursor-pointer uppercase hover:text-[#5B8EAE] hover:[text-shadow:0.04em_0_currentColor,-0.04em_0_currentColor]"
        >
            {{ $item['name'] }}
            <span class="opacity-60">[{{ $item['count'] }}]</span>
        </button>
    @endforeach
</div>
