@props([
    'url' => '#',
    'label' => 'подробнее',
])

<a href="{{ $url }}" class="btn-corner btn-corner-color group">
    <div
        class="btn-corner-color absolute top-[3px] left-[-3px] h-[1px] min-h-[1px] w-[64px] min-w-[64px] -rotate-45 border-b transition-all duration-300"
    ></div>

    <span class="text-btn-corner relative z-10 pl-2.5 text-white">{{ $label }}</span>

    <div
        class="btn-corner-color absolute right-[-3px] bottom-[3px] h-[1px] min-h-[1px] w-[12px] min-w-[12px] -rotate-45 border-b transition-all duration-300"
    ></div>
</a>
