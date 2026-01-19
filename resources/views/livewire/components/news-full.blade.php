<div id="news-block" class="relative max-w-1242 w-full mx-auto my-inner-section-y px-inner-section-x">
    
    @if($categoryItems->count() > 1)
        {{-- Mobile --}}
        <x-other.categories-select :items="$categoryItems" :active="$activeCategory"/>
        {{-- Desktop --}}
        <x-other.categories-list :items="$categoryItems" :active="$activeCategory"/>
    @endif
    
    <div class="flex flex-col gap-y-15 gap-x-32">
        @foreach($cards as $card)
            <article class="relative w-full grid md:grid-cols-2 gap-6">
                <div>
                    <img
                            src="{{ $card['thumbnail'] }}"
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
                        
                        <x-buttons.btn-more
                                url="{{ $card['url']}}"
                                text="подробнее"
                                class="text-accent-add hover:text-[#5B8EAE]"
                        />
                    </div>
                </div>
            </article>
        @endforeach
        
        <div class="mt-5">
            {{ $cards->links('livewire::tailwind', data: ['scrollTo' => '#news-block']) }}
        </div>
    </div>
</div>