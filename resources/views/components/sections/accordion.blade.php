@props([
    'accordions' =>
    [
        'title' => 'Что нужно сделать для разработки и согласования проекта генплана',
        'type' => 'white',
        'accordions' =>
        [
            
            [
            'point' => 'ЭТАП I',
            'itemTitle' => 'ПОЛУЧЕНИЕ ГОСУСЛУГИ',
            'itemDescription' => '// До 17 рабочих дней',
            'items' => [
                    [
                        'type' => 'question',
                        'data' => [
                            'title'       => 'Как получить Госуслугу?',
                            'description' => 'Подать заявку на сайте госуслуг Московской области (http://gosuslugi.ru).',
                            'btnUrl'      => '#',
                            'btnLabel'    => 'ПОДАТЬ ЗАЯВКУ НА госуслугах',
                        ],
                    ],
                    [
                        'type' => 'plus',
                        'data' => [
                            'title'       => 'Для получения госуслуги может понадобится “Концепция развития территории”.',
                            'description' => 'В случае необходимости, концепцию развития территории можно заказать у нас.',
                            'btnUrl'      => '#',
                            'btnLabel'    => 'ЗАКАЗТЬ КОНЦЕПЦИЮ РАЗВИТИЯ',
                        ],
                    ],
                    [
                        'type' => 'info',
                        'data' => [
                            'title'       => 'Зачем нужна Госуслуга?',
                            'description' => 'Для принятия решения об учете предложений физических и юридических лиц...',
                        ],
                    ],
                ],
            ]
        ]
    ]
])

<section class="max-w-1242 w-full mx-auto my-inner-section-y px-inner-section-x">
    
    <h2 class="mb-after-title text-primary dark:text-accent-add-dark">{{$accordions['title']}}</h2>
    <div class="space-y-4">
        
        @foreach($accordions['accordions'] as $accordion)
            
            <x-other.accordion-item
                    type="{{$accordions['type']}}"
                    is-open="{{false}}"
            >
                <x-other.accordion-button
                        point="{{$accordion['point']}}"
                        item-title="{{$accordion['itemTitle']}}"
                        item-description="{{$accordion['itemDescription']}}"
                        type="{{$accordions['type']}}"
                />
                
                @foreach($accordion['items']  as $item)
                    
                    <x-other.accordion-description>
                        <x-other.accordion-item-description class="acc-item-bg-{{$item['type']}}">
                            
                            <div class="flex flex-wrap md:flex-nowrap items-start flex-col gap-0">
                                <div class="flex flex-raw gap-4">
                                    <div class="min-w-[26px] min-h-[26px] w-7 h-7 icon-{{$item['type']}} bg-no-repeat bg-center"></div>
                               
                                    <p class="text-big text-text dark:text-white-dark font-bold">{{$item['data']['title']}}</p>
                                </div>
                                <div class="pl-11 text-normal text-text dark:text-white-dark">{!! $item['data']['description'] !!}</div>
                            </div>
                            
                            @if(isset($item['data']['btnUrl']))
                                <x-buttons.btn url="{{$item['data']['btnUrl']}}"
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