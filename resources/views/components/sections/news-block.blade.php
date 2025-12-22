@props([
    'title' => '',
    'btnUrl' => '',
    'btnLabel' => '',
    'bgImageUrl' => '',
  
])

<x-layout.section-full bg-image-url="{{$data['bgImageUrl']}}">
    <h2 class="text-white">{{$title}}</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-10 gap-x-32">
        @foreach($cards as $card)
            <article class="relative w-full">
                <h3 class="text-white pb-1 border-b border-b-white line-clamp-2 whitespace-normal">
                    {{$card['title']}}
                </h3>
                
                <p class="mt-5 text-normal text-white line-clamp-4">
                    {{$card['description']}}
                </p>
                
                <div class="mt-6 flex items-end justify-between gap-4">
                    <span class="text-small text-accent-add">
                        {{$card['publishedAt']}}
                    </span>
                    <x-buttons.btn-more url="{{$card['url']}}" text="подробнее"/>
                </div>
            </article>
        @endforeach
    </div>
    
    <x-buttons.btn url="{{$data['btnUrl']}}" text="{{$data['btnLabel']}}" type="btn-secondary" class="self-end"/>
</x-layout.section-full>

