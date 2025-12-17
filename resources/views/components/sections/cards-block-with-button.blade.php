@props([
    'title' => '// УСЛУГИ',
    'btnUrl' => '#',
    'btnLabel' => 'все услуги',
    'cards' =>
    [
        [
            'cardBtnUrl' => '#',
            'cardBtnLabel' => 'подробнее',
            'cardTitle' => '01 // ГЕНЕРАЛЬНЫЙ ПЛАН',
            'cardDescription' => 'Генеральный план — документ
             территориального планирования, который определяет развитие муниципального образования на долгосрочную перспективу.'
       ],
        [
            'cardBtnUrl' => '#',
            'cardBtnLabel' => 'подробнее',
            'cardTitle' => '02 // ПЗЗ',
            'cardDescription' => 'Документ необходимый для изменения границ территориальных зон и градостроительных регламентов.'
       ],
        [
            'cardBtnUrl' => '#',
            'cardBtnLabel' => 'подробнее',
            'cardTitle' => '03 // Схема транспортного обслуживания',
            'cardDescription' => 'Схема позволяет спрогнозировать уровни нагрузки на объектах дорожной инфраструктуры с учётом перспективного ввода в эксплуатацию жилых застроек.'
       ],
        [
            'cardBtnUrl' => '#',
            'cardBtnLabel' => 'подробнее',
            'cardTitle' => '04 // Планировка территории линейного объекта',
            'cardDescription' => 'Документ необходимый для обоснования строительства линейных объектов, таких как дороги, трубопроводы или линии электропередачи, и для получения разрешения на их строительство.'
       ],
        [
            'cardBtnUrl' => '#',
            'cardBtnLabel' => 'подробнее',
            'cardTitle' => '05 // Архитектурно - градостроительная концепция',
            'cardDescription' => 'Концепция необходима для определения основных принципов развития территории и создания основы для дальнейшего проектирования.'
       ],
    ],
])


<section class="container flex flex-col mx-auto my-inner-section-y px-inner-section-x max-w-1242 w-full">
    
    <h2 class="mb-after-title text-primary">{{$title}}</h2>
    
    <div class="flex flex-row flex-wrap gap-[37px] justify-between">
        
        @foreach($cards['cards'] as $card)
            <x-other.small-card-button btn-url="{{$card['cardBtnUrl']}}"
                                       btn-label="{{$card['cardBtnLabel']}}"
                                       card-title="{{$card['cardTitle']}}"
                                       card-description="{{$card['cardDescription']}}"
            />
        @endforeach
        
        <div class="relative flex justify-end items-end max-w-[368px] w-full">
            <x-buttons.btn url="{{$btnUrl}}"
                           text="{{$btnLabel}}"
                           type="btn-primary"
            />
        </div>
        
    </div>
    
</section>