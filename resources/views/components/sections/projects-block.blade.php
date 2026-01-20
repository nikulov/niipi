<x-layout.section-full bg-image-url="{{$data['bgImageUrl']}}">
    <h2 class="text-white dark:text-accent-add-dark">{{$data['title']}}</h2>
    
    <div class="grid grid-cols-1 md:grid-cols-2 gap-y-15 md:gap-y-10 gap-x-32">
        @foreach($cards as $card)
        <div class="relative w-full flex flex-col">
            
            <a href="{{$card['url']}}">
                <div class="relative">
                    <img src="{{$card['thumbnail']}}" alt="" class="w-full h-[300px] object-cover bg-primary/20">
                    <div class="absolute inset-0 bg-primary/20"></div>
                </div>
                
                <div class="flex flex-col">
                    <p class="mt-5 text-normal uppercase text-white line-clamp-3">
                        {{$card['title']}}
                    </p>
                </div>
            </a>
            
        </div>
        @endforeach
    </div>
    
    <x-buttons.btn url="{{$data['btnUrl']}}" text="{{$data['btnLabel']}}" type="btn-secondary" class="self-center md:self-end"/>
</x-layout.section-full>