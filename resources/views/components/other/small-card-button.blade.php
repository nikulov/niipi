@props([
    'btnUrl' => '#',
    'btnLabel' => 'подробнее',
    'cardTitle' => '01 // Заголовок карточки',
    'cardDescription' => 'Многие думают, что Lorem Ipsum - взятый с потолка псевдо-латинский набор слов, но это не совсем так. Его корни уходят в один фрагмент классической латыни 45 года н.э., то есть более двух тысячелетий назад. Ричард МакКлинток, профессор латыни из колледжа Hampden-Sydney, штат Вирджиния, взял одно из самых странных слов в Lorem Ipsum, "consectetur", и занялся его поисками в классической латинской литературе.',
])

<div class="relative w-full max-w-full md:max-w-92">
    <div class="card-wrapper dark:bg-background-dark bg-white">
        <div
            class="bg-primary dark:bg-accent absolute top-0 left-0 h-1.5 w-full"
            style="clip-path: polygon(6px 0, 100% 0, 100% 100%, 100% 100%, 0 100%, 0 6px)"
        ></div>

        <div class="border-primary dark:border-accent absolute top-0.75 -left-1.25 z-30 h-px min-h-px w-3 min-w-3 -rotate-45 border"></div>

        <h3 class="text-primary dark:text-accent-add-dark relative z-10 mb-3">{{ $cardTitle }}</h3>
        <p class="text-normal dark:text-text-dark relative z-10">{{ $cardDescription }}</p>

        <div class="bg-primary dark:bg-accent absolute right-0 bottom-7.25 z-30 h-px w-full max-w-27.5"></div>
        <div
            class="border-primary dark:border-accent absolute right-25.75 bottom-3.25 z-30 h-px min-h-px w-11.5 min-w-11.5 -rotate-45 border-b"
        ></div>
    </div>
    <x-buttons.btn-corner url="{{$btnUrl}}" label="{{$btnLabel}}" />
</div>
