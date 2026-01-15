<div id="news-block" class="relative max-w-1242 w-full mx-auto my-inner-section-y px-inner-section-x">
    
    @if($categories->isNotEmpty())
        <div class="relative flex flex-wrap justify-center gap-8 mb-20">
            <button
                    type="button"
                    wire:click="setCategory(null)"
                    class="relative cursor-pointer uppercase
                    {{ $activeCategory === null ? 'text-accent' : 'text-primary' }}"
            >
                Все
                <span class="opacity-60">
                    [{{$totalProjectsCount}}]
                </span>
            </button>
            
            @foreach($categories as $cat)
                <button
                        type="button"
                        wire:click="setCategory('{{ $cat->slug }}')"
                        class="relative cursor-pointer uppercase pl-8
                        before:content-[''] before:absolute before:-left-px
                        before:w-1 before:h-5
                        before:bg-[url('/resources/images/layout/acc-dots-dark.svg')]
                        {{ $activeCategory === $cat->slug ? 'text-accent' : 'text-primary' }}"
                >
                    {{ $cat->name }}
                    <span class="opacity-60">
                        [{{$cat->projects_count}}]
                    </span>
                </button>
            @endforeach
        </div>
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
                                class="text-[#324B60] hover:text-[#5B8EAE]"
                        />
                    </div>
                </div>
            </article>
        @endforeach
        
        <div>
            {{ $cards->links('livewire::tailwind', data: ['scrollTo' => '#news-block']) }}
        </div>
    </div>
</div>