@props([
    'url' => '#',
    'text' => 'подробнее',
])

<a href="{{ $url }}" {{ $attributes->class('text-big relative mr-3 flex items-center gap-1 font-bold transition-colors duration-300') }}>
    <span class="relative after:font-bold">[&nbsp;{{ $text }}&nbsp;]</span>
    <x-icon.icon-arrow-down-add class="absolute -top-1 -right-3 h-2.5 w-2.5" />
</a>
