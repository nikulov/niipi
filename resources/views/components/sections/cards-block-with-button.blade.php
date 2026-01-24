@props([
    'title' => '',
    'btnUrl' => '',
    'btnLabel' => '',
    'cards' => [
        [
            'cardBtnUrl' => '#',
            'cardBtnLabel' => 'подробнее',
            'cardTitle' => '01 // ГЕНЕРАЛЬНЫЙ ПЛАН',
            'cardDescription' => 'Генеральный план — документ
             территориального планирования, который определяет развитие муниципального образования на долгосрочную перспективу.',
        ],
        [
            'cardBtnUrl' => '#',
            'cardBtnLabel' => 'подробнее',
            'cardTitle' => '02 // ПЗЗ',
            'cardDescription' => 'Документ необходимый для изменения границ территориальных зон и градостроительных регламентов.',
        ],
        [
            'cardBtnUrl' => '#',
            'cardBtnLabel' => 'подробнее',
            'cardTitle' => '03 // Схема транспортного обслуживания',
            'cardDescription' => 'Схема позволяет спрогнозировать уровни нагрузки на объектах дорожной инфраструктуры с учётом перспективного ввода в эксплуатацию жилых застроек.',
        ],
        [
            'cardBtnUrl' => '#',
            'cardBtnLabel' => 'подробнее',
            'cardTitle' => '04 // Планировка территории линейного объекта',
            'cardDescription' => 'Документ необходимый для обоснования строительства линейных объектов, таких как дороги, трубопроводы или линии электропередачи, и для получения разрешения на их строительство.',
        ],
        [
            'cardBtnUrl' => '#',
            'cardBtnLabel' => 'подробнее',
            'cardTitle' => '05 // Архитектурно - градостроительная концепция',
            'cardDescription' => 'Концепция необходима для определения основных принципов развития территории и создания основы для дальнейшего проектирования.',
        ],
    ],
])

<section class="my-inner-section-y px-inner-section-x container mx-auto flex w-full max-w-1242 flex-col">
    @if ($title)
        <h2 class="mb-after-title text-primary dark:text-accent-add-dark">{{ $title }}</h2>
    @endif

    <div class="flex flex-row flex-wrap justify-center gap-9.25 xl:justify-start">
        @foreach ($cards as $card)
            <x-other.small-card-button
                btn-url="{{$card['cardBtnUrl']}}"
                btn-label="{{$card['cardBtnLabel']}}"
                card-title="{{$card['cardTitle']}}"
                card-description="{{$card['cardDescription']}}"
            />
        @endforeach

        @if ($btnLabel && $btnUrl)
            <div class="relative flex w-full items-end justify-center md:max-w-92 md:justify-end">
                <x-buttons.btn url="{{$btnUrl}}" text="{{$btnLabel}}" type="btn-primary" />
            </div>
        @endif
    </div>
</section>
