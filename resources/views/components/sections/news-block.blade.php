<x-layout.section-full bg-image-url="{{$data['bgImageUrl']}}">
    <h2 class="text-white">{{$data['title']}}</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-10 gap-x-32">
        @foreach($cards as $card)
            <article class="relative w-full">
                <h3 class="text-white normal-case pb-1 border-b border-b-white line-clamp-2 whitespace-normal">
                    {{$card['title']}}
                </h3>
                
                <p class="mt-5 text-normal text-white line-clamp-4">
                    {{$card['description']}}
                </p>
                
                <div class="mt-6 flex items-end justify-between gap-4">
                    <span class="text-small text-[#CBDEEA]">
                        {{$card['publishedAt']}}
                    </span>
                    <x-buttons.btn-more url="{{$card['url']}}" text="подробнее" class="text-accent-add hover:text-[#9DDEE0]"/>
                </div>
            </article>
        @endforeach
    </div>
    
    <x-buttons.btn url="{{$data['btnUrl']}}" text="{{$data['btnLabel']}}" type="btn-secondary" class="self-center md:self-end"/>
</x-layout.section-full>

