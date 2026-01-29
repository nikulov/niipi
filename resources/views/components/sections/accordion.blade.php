@props([
    'accordions' => [
        'title' => 'Что нужно сделать для разработки и согласования проекта генплана',
        'type' => 'white',
        'accordions' => [
            [
                'point' => 'ЭТАП I',
                'itemTitle' => 'ПОЛУЧЕНИЕ ГОСУСЛУГИ',
                'itemDescription' => '// До 17 рабочих дней',
                'items' => [
                    [
                        'type' => 'question',
                        'data' => [
                            'title' => 'Как получить Госуслугу?',
                            'description' => 'Подать заявку на сайте госуслуг Московской области (http://gosuslugi.ru).',
                            'btnUrl' => '#',
                            'btnLabel' => 'ПОДАТЬ ЗАЯВКУ НА госуслугах',
                        ],
                    ],
                    [
                        'type' => 'plus',
                        'data' => [
                            'title' => 'Для получения госуслуги может понадобится “Концепция развития территории”.',
                            'description' => 'В случае необходимости, концепцию развития территории можно заказать у нас.',
                            'btnUrl' => '#',
                            'btnLabel' => 'ЗАКАЗТЬ КОНЦЕПЦИЮ РАЗВИТИЯ',
                        ],
                    ],
                    [
                        'type' => 'info',
                        'data' => [
                            'title' => 'Зачем нужна Госуслуга?',
                            'description' => 'Для принятия решения об учете предложений физических и юридических лиц...',
                        ],
                    ],
                ],
            ],
        ],
    ],
])

<section class="my-inner-section-y px-inner-section-x mx-auto w-full max-w-1242">
    <h2 class="mb-after-title text-primary dark:text-accent-add-dark">{!! nl2br(e($accordions['title'])) !!}</h2>
    <div class="space-y-4">
        @foreach ($accordions['accordions'] as $accordion)
            <x-other.accordion-item type="{{$accordions['type']}}" :is-open="false">
                <x-other.accordion-button
                    point="{{$accordion['point']}}"
                    item-title="{{$accordion['itemTitle']}}"
                    item-description="{{$accordion['itemDescription']}}"
                    type="{{$accordions['type']}}"
                />

                @foreach ($accordion['items'] as $item)
                    <x-other.accordion-description>
                        <x-other.accordion-item-description class="acc-item-bg-{{$item['type']}}">
                            <div class="flex flex-col flex-wrap items-start gap-0 md:flex-nowrap">
                                <div class="flex-raw flex gap-4">
                                    <x-other.accordion-item-icon
                                        type="{{$item['type']}}"
                                        class="fill-primary dark:fill-accent-add-dark max-h-7 min-h-7 max-w-7 min-w-7"
                                    />
                                    <p class="text-big text-text dark:text-white-dark font-bold">{{ $item['data']['title'] }}</p>
                                </div>
                                <div class="text-normal text-text dark:text-white-dark md:pl-11">{!! $item['data']['description'] !!}</div>
                            </div>

                            @if (isset($item['data']['btnUrl']))
                                <x-buttons.btn
                                    url="{{$item['data']['btnUrl']}}"
                                    text="{{$item['data']['btnLabel']}}"
                                    type="btn-transparent"
                                    class="mx-auto md:mx-0 md:ml-auto"
                                />
                            @endif
                        </x-other.accordion-item-description>
                    </x-other.accordion-description>
                @endforeach
            </x-other.accordion-item>
        @endforeach
    </div>
</section>
