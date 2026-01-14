@props([
    'btnUrl' => '#',
    'btnLabel' => 'подробнее',
    'cardTitle' => '01 // Заголовок карточки',
    'cardDescription' => 'Многие думают, что Lorem Ipsum - взятый с потолка псевдо-латинский набор слов, но это не совсем так. Его корни уходят в один фрагмент классической латыни 45 года н.э., то есть более двух тысячелетий назад. Ричард МакКлинток, профессор латыни из колледжа Hampden-Sydney, штат Вирджиния, взял одно из самых странных слов в Lorem Ipsum, "consectetur", и занялся его поисками в классической латинской литературе.'
])

<div class="relative max-w-full md:max-w-[368px] w-full">
    
    <div class="card-wrapper

">
        <div class="absolute top-0 left-0 bg-primary w-full h-1.5"
             style="
                    clip-path: polygon(
                            6px 0,
                            100% 0,
                            100% 100%,
                            100% 100%,
                            0 100%,
                            0 6px);">
        </div>
        
        <div class="absolute top-[3px] left-[-5px] -rotate-45 min-w-3 w-3 min-h-px h-px border border-primary  transition-all duration-300 z-30"></div>
        
        <h3 class="relative mb-3 z-10 text-primary">{!!$cardTitle!!}</h3>
        <p class="relative text-normal z-10">{!!$cardDescription!!}</p>
        
        
        <div class="absolute bottom-[29px] right-0 h-px max-w-[110px] w-full bg-primary z-30"></div>
        <div class="absolute bottom-[13px] right-[103px] -rotate-45 min-w-[46px] w-[46px] min-h-px h-px border-b transition-all duration-300 border-primary z-30"></div>
    
    </div>
    <x-buttons.btn-corner url="{{$btnUrl}}" label="{{$btnLabel}}"/>
</div>
