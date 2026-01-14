<div id="news-block" class="relative max-w-1242 w-full mx-auto my-inner-section-y px-inner-section-x">
    
    @if($categories->count() > 0)
        <div class="flex flex-wrap gap-3 mb-10">
            <button
                    type="button"
                    wire:click="setCategory(null)"
                    class="{{ $activeCategory === null ? 'text-accent' : 'text-text' }} cursor-pointer"
            >
                Все
            </button>
            
            @foreach($categories as $cat)
                <button
                        type="button"
                        wire:click="setCategory('{{ $cat->slug }}')"
                        class="{{ $activeCategory === $cat->slug ? 'text-accent' : 'text-text' }} cursor-pointer"
                >
                    {{ $cat->name }}
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
                        {{ $card['title'] }}
                    </h3>
                    
                    <p class="mt-5 text-normal text-text line-clamp-4">
                        {{ $card['description'] }}
                    </p>
                    
                    <div class="pt-6 mt-auto flex items-end justify-between gap-4">
                        <span class="text-small text-accent">
                            {{ $card['publishedAt'] }}
                        </span>
                        
                        <x-buttons.btn-more
                                url="{{ $card['url'] }}"
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