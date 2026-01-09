<?php

?>

<div class="relative max-w-1242 w-full mx-auto my-inner-section-y px-inner-section-x">
    
    <div class="flex flex-col gap-y-10 gap-x-32">
        @foreach($cards as $card)
            
            <article class="relative w-full grid md:grid-cols-2 gap-6">
                <div class="">
                    
                    <img src="{{$card['thumbnail']}}"
                         alt=""
                         class="w-full h-[180px] md:h-[360px] object-cover"
                    >
                
                </div>
                
                <div class="flex flex-col">
                    
                    <h3 class="text-primary pb-1 whitespace-normal">
                        {{$card['title']}}
                    </h3>
                    
                    <p class="mt-5 text-normal text-text line-clamp-4">
                        {{$card['description']}}
                    </p>
                    
                    <div class="pt-6 mt-auto flex items-end justify-between gap-4">
                        <span class="text-small text-accent">
                            {{$card['publishedAt']}}
                        </span>
                        <x-buttons.btn url="{{$card['url']}}" text="подробнее" type="btn-secondary"/>
                    </div>
                
                </div>
            
            </article>
        @endforeach
            
            {{ $cards->links() }}
        
    </div>


</div>