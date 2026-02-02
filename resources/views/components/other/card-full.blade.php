@props([
    'card',
])

<div {{ $attributes->merge(['class' => 'flex flex-col']) }}>
    <a href="{{ $card['url'] }}" class="hover:[text-shadow:0.02em_0_currentColor,-0.02em_0_currentColor]">
        <h3 class="text-primary dark:text-white-dark pb-1 whitespace-normal">
            {{ $card['title'] }}
        </h3>
    </a>

    <p class="text-normal text-text dark:text-text-dark mt-5 line-clamp-4">
        {{ $card['description'] }}
    </p>

    <div class="mt-auto flex items-end justify-between gap-4 pt-6">
        <span class="text-small text-accent">
            {{ $card['publishedAt'] }}
        </span>

        <x-buttons.btn-more url="{{ $card['url'] }}" text="подробнее" class="text-accent-add hover:text-[#5B8EAE]" />
    </div>
</div>
