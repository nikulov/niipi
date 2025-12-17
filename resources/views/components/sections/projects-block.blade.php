@props([
'title' => '// проекты',
'btnUrl' => '#',
'btnText' => 'все проекты',
'projectUrl' => '#',
'projectImageUrl' => '/images/projectimage1.jpg',
'projectDescription' => 'Многие думают, что Lorem Ipsum - взятый с потолка псевдо-латинский набор слов, но это не совсем так. Его корни уходят в один фрагмент классической латыни 45 года н.э., то есть более двух тысячелетий назад. Ричард МакКлинток, профессор латыни из колледжа Hampden-Sydney, штат Вирджиния, взял одно из самых странных слов в Lorem Ipsum, "consectetur", и занялся его поисками в классической латинской литературе.',
])

<x-layout.section-full bg-image-url="/images/layout/bg-project.png">
    <h2 class="text-white">{{$title}}</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-10 gap-x-32">
        @for($news = 1; $news <= 4; $news++)
        <div class="relative w-full flex flex-col">
            
            <div class="relative">
                <img src="{{$projectImageUrl}}" alt="" class="w-full h-[300px] object-cover bg-primary/20">
                <div class="absolute inset-0 bg-primary/20"></div>
            </div>
            
            <div class="flex flex-col">
                <p class="mt-5 text-normal text-white line-clamp-3">
                    {{$projectDescription}}
                </p>
                
                <div class="mt-6 flex items-end justify-end gap-4">
                    <x-buttons.btn-more url="{{$projectUrl}}" text="подробнее"/>
                </div>
            </div>
            
        </div>
        @endfor
    </div>
    
    <x-buttons.btn url="{{$btnUrl}}" text="{{$btnText}}" type="btn-secondary" class="self-end"/>
</x-layout.section-full>