@props([
    'items',
    'active' => null,
])

<div {{ $attributes->merge(['class' => 'relative hidden md:flex flex-wrap justify-center gap-8 mb-20']) }}>
    @foreach($items as $item)
        <button
                type="button"
                wire:click="setCategory({{ $item['slug'] ? "'{$item['slug']}'" : 'null' }})"
                class="relative cursor-pointer uppercase change-theme
            {{ $item['slug'] ? 'pl-8 before:content-[\'\'] before:absolute before:-left-px before:w-1 before:h-5 before:bg-[url(\'/resources/images/layout/acc-dots-dark.svg\')]' : '' }}
            {{ $active === $item['slug'] ? 'text-accent dark:text-accent-add-dark font-bold' : 'text-primary dark:text-accent-add-dark' }}"
        >
            {{ $item['name'] }}
            <span class="opacity-60">
                [{{ $item['count'] }}]
            </span>
        </button>
    @endforeach
</div>