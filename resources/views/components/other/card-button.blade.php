@props([
    'btnUrl' => '#',
    'btnLabel' => 'подробнее',
    'cardTitle' => '',
    'cardDescription' => '',
])

<a href="{{ $btnUrl }}" class="group">
    <div class="relative h-full w-full max-w-full md:max-w-92">
        <div class="card-wrapper dark:bg-background-dark bg-white">
            <div
                class="bg-primary dark:bg-accent absolute -top-px -left-0.5 h-1.5 w-[calc(100%+2px)]"
                style="clip-path: polygon(6px 0, 100% 0, 100% 100%, 100% 100%, 0 100%, 0 6px)"
            ></div>

            <div
                class="border-primary dark:border-accent absolute top-0.75 -left-1.25 z-30 h-px min-h-px w-3 min-w-3 -rotate-45 border"
            ></div>

            <h3 class="text-primary dark:text-accent-add-dark relative z-10 mb-3">{{ $cardTitle }}</h3>
            <p class="text-normal dark:text-text-dark relative z-10">{{ $cardDescription }}</p>

            <div class="bg-primary dark:bg-accent card-wrapper-shadow absolute right-0 bottom-7.25 z-30 h-px w-full max-w-27.5"></div>
            <div
                class="border-primary dark:border-accent card-wrapper-shadow absolute right-25.75 bottom-3.25 z-30 h-px min-h-px w-11.5 min-w-11.5 -rotate-45 border-b"
            ></div>
        </div>

        <div class="btn-corner btn-corner-color">
            <div
                class="btn-corner-color absolute top-0.75 -left-0.75 h-px min-h-px w-16 min-w-16 -rotate-45 border-b transition-all duration-300"
            ></div>

            <span class="text-btn-corner relative z-10 pl-2.5 text-white">{{ $btnLabel }}</span>

            <div
                class="btn-corner-color absolute -right-0.75 bottom-0.75 h-px min-h-px w-3 min-w-3 -rotate-45 border-b transition-all duration-300"
            ></div>
        </div>
    </div>
</a>
