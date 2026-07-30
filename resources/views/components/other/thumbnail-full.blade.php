@props([
    'cardUrl' => '#',
    'cardThumbnail' => '',
    'cardTitle' => '',
])

<div>
    <a href="{{ $cardUrl }}" class="group relative block overflow-hidden">
        <img
            src="{{ $cardThumbnail }}"
            alt="{{ $cardTitle }}"
            class="h-45 w-full object-cover transition-transform duration-500 ease-in-out group-hover:scale-105 md:h-90"
        />
    </a>
</div>
