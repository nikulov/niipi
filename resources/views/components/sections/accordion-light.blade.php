@props([
    'accordions' => [
        'title' => 'КАКИЕ МАТЕРИАЛЫ ВЫ ПОЛУЧИТЕ',
        'type' => 'dark',
        'accordions' => [
            [
                'itemTitle2' => 'Утверждаемая часть',
                'item' => ' Положение о территориальном планировании
                        Карта границ населенных пунктов
                        Карта функциональных зон муниципального образования
                        Приложение. Сведения о границах населенных пунктов',
            ],

            [
                'itemTitle2' => 'Материалы по обоснованию',

                'item' => '<article>
            <h3 class="text-big font-bold text-text">
                ТОМ 1. Планировочная и инженерно-транспортная организация территории. Социально-экономическое обоснование
            </h3>

            <p class="text-normal text-muted">
                Содержит текстовую часть и графические материалы по планировочной структуре, использованию территории и развитию инфраструктуры.
            </p>

            <div class="space-y-2">
                <p class="text-normal font-semibold text-text">
                    Текстовая часть
                </p>

                <p class="text-normal font-semibold text-text mt-4">
                    Карты:
                </p>
                <ul class="list-disc pl-5 space-y-1 text-normal text-text">
                    <li>Карта размещения муниципального образования в устойчивой системе расселения Московской области</li>
                    <li>Карта существующего использования территории</li>
                    <li>Карта планируемого развития транспортной инфраструктуры</li>
                    <li>Карта зон с особыми условиями использования территории</li>
                    <li>Карта границ земель лесного фонда с отображением границ лесничеств и лесопарков</li>
                    <li>Карта границ земель сельскохозяйственного назначения с отображением особо ценных сельскохозяйственных угодий и мелиорируемых земель</li>
                </ul>
            </div>
        </article>',
            ],
        ],
    ],
])

<section class="my-inner-section-y px-inner-section-x relative mx-auto w-full max-w-1242">
    <h2 class="mb-after-title text-primary dark:text-accent-add-dark">{{ $accordions['title'] }}</h2>
    <div class="space-y-4">
        @foreach ($accordions['accordions'] as $accordion)
            <x-other.accordion-item
                type="{{$accordions['type']}}"
                :is-open="$loop->first && $accordions['type'] === 'dark' && $loop->count > 1"
            >
                <x-other.accordion-button item-title="{{$accordion['itemTitle2']}}" type="{{$accordions['type']}}" />

                <x-other.accordion-description>
                    <x-other.accordion-item-description
                        class="text-normal text-text dark:text-text-dark bg-background-light dark:bg-background-dark-add flex-col!"
                    >
                        {!! $accordion['item'] !!}
                    </x-other.accordion-item-description>
                </x-other.accordion-description>
            </x-other.accordion-item>
        @endforeach
    </div>
</section>
