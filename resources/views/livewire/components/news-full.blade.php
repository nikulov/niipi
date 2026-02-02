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
                <x-other.thumbnail-full card-url="{{ $card['url'] }}" card-thumbnail="{{ $card['thumbnail'] }}" />

                <x-other.card-full :card="$card" />
            </article>
        @endforeach

        <div class="mt-5">
            {{ $cards->links('livewire::tailwind', data: ['scrollTo' => '#news-block']) }}
        </div>
    </div>
</div>
