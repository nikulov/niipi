@props([
    'url' => '#',
    'label' => 'подробнее',
])

<a href="{{ $url }}" class="btn-corner btn-corner-color group">
    <div
        class="btn-corner-color absolute top-0.75 -left-0.75 h-px min-h-px w-16 min-w-16 -rotate-45 border-b transition-all duration-300"
    ></div>

    <span class="text-btn-corner relative z-10 pl-2.5 text-white">{{ $label }}</span>

    <div
        class="btn-corner-color absolute -right-0.75 bottom-0.75 h-px min-h-px w-3 min-w-3 -rotate-45 border-b transition-all duration-300"
    ></div>
</a>
