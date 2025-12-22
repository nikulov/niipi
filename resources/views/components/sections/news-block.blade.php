@props([
    'title' => '',
    'btnUrl' => '',
    'btnLabel' => '',
    'bgImageUrl' => '',
    'posts' => [
        [
            'published_at' => '01.01.2026',
            'slug' => '#',
            'title' => 'Заголовок новости',
            'description' => 'Многие думают, что Lorem Ipsum - взятый с потолка псевдо-латинский набор слов, но это не совсем так. Его корни уходят в один фрагмент классической латыни 45 года н.э., то есть более двух тысячелетий назад. Ричард МакКлинток, профессор латыни из колледжа Hampden-Sydney, штат Вирджиния, взял одно из самых странных слов в Lorem Ipsum, "consectetur", и занялся его поисками в классической латинской литературе.',
        ],
        
    ],
    
    
])

<x-layout.section-full bg-image-url="{{$data['bgImageUrl']}}">
    <h2 class="text-white">{{$title}}</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-10 gap-x-32">
        @foreach($posts as $post)
            <article class="relative w-full">
                <h3 class="text-white pb-1 border-b border-b-white line-clamp-2 whitespace-normal">
                    {{$post['title']}}
                </h3>
                
                <p class="mt-5 text-normal text-white line-clamp-4">
                    {{$post['description']}}
                </p>
                
                <div class="mt-6 flex items-end justify-between gap-4">
                    <span class="text-small text-accent-add">
                        {{$post['published_at']}}
                    </span>
                    <x-buttons.btn-more url="{{'news/' . $post['slug']}}" text="подробнее"/>
                </div>
            </article>
            @endforeach
    </div>
    
    <x-buttons.btn url="{{$data['btnUrl']}}" text="{{$data['btnLabel']}}" type="btn-secondary" class="self-end"/>
</x-layout.section-full>

