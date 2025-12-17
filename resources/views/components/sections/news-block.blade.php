@props([
    'title' => '// новости',
    'btnUrl' => '#',
    'btnText' => 'все новости',
    'newsDate' => '01.01.2026',
    'newsUrl' => '#',
    'newsTitle' => 'Заголовок новости',
    'newsDescription' => 'Многие думают, что Lorem Ipsum - взятый с потолка псевдо-латинский набор слов, но это не совсем так. Его корни уходят в один фрагмент классической латыни 45 года н.э., то есть более двух тысячелетий назад. Ричард МакКлинток, профессор латыни из колледжа Hampden-Sydney, штат Вирджиния, взял одно из самых странных слов в Lorem Ipsum, "consectetur", и занялся его поисками в классической латинской литературе.',
])

<x-layout.section-full bg-image-url="images/layout/bg-news.png">
    <h2 class="text-white">{{$title}}</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-10 gap-x-32">
        @for($news = 1; $news <= 4; $news++)
            <article class="relative w-full">
                <h3 class="text-white pb-1 border-b border-b-white ">
                    {{$newsTitle}}
                </h3>
                
                <p class="mt-5 text-normal text-white line-clamp-4">
                    {{$newsDescription}}
                </p>
                
                <div class="mt-6 flex items-end justify-between gap-4">
                    <span class="text-small text-accent-add">
                        {{$newsDate}}
                    </span>
                    <x-buttons.btn-more url="{{$newsUrl}}" text="подробнее"/>
                </div>
            </article>
        @endfor
    </div>
    
    <x-buttons.btn url="{{$btnUrl}}" text="{{$btnText}}" type="btn-secondary" class="self-end"/>
</x-layout.section-full>

