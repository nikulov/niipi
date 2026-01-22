<div id="news-block" class="my-inner-section-y px-inner-section-x relative mx-auto w-full max-w-1242">
    @if ($categoryItems->count() > 1)
        {{-- Mobile --}}
        <x-other.categories-select :items="$categoryItems" :active="$activeCategory" />
        {{-- Desktop --}}
        <x-other.categories-list :items="$categoryItems" :active="$activeCategory" />
    @endif

    <div class="flex flex-col gap-x-32 gap-y-15">
        @foreach ($cards as $card)
            <article class="relative grid w-full gap-6 md:grid-cols-2">
                <div>
                    <img src="{{ $card['thumbnail'] }}" alt="" class="h-45 w-full object-cover md:h-90" />
                </div>

                <div class="flex flex-col">
                    <h3 class="text-primary dark:text-white-dark pb-1 whitespace-normal">
                        {{ $card['title'] }}
                    </h3>

                    <p class="text-normal text-text dark:text-white-dark mt-5 line-clamp-4">
                        {{ $card['description'] }}
                    </p>

                    <div class="mt-auto flex items-end justify-between gap-4 pt-6">
                        <span class="text-small text-accent">
                            {{ $card['publishedAt'] }}
                        </span>

                        <x-buttons.btn-more url="{{ $card['url']}}" text="подробнее" class="text-accent-add hover:text-[#9DDEE0]" />
                    </div>
                </div>
            </article>
        @endforeach

        <div class="mt-5">
            {{ $cards->links('livewire::tailwind', data: ['scrollTo' => '#news-block']) }}
        </div>
    </div>
</div>
