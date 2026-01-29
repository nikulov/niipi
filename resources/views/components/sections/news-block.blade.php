<x-layout.section-full bg-image-url="{{$data['bgImageUrl']}}">
    <h2 class="dark:text-accent-add-dark text-white">
        {!! nl2br(e($data['title'])) !!}
    </h2>

    <div class="grid grid-cols-1 gap-x-32 gap-y-20 md:grid-cols-2 md:gap-y-10">
        @foreach ($cards as $card)
            <article class="relative w-full">
                <h3 class="line-clamp-2 border-b border-b-white pb-1 whitespace-normal text-white normal-case">
                    {{ $card['title'] }}
                </h3>

                <p class="text-normal mt-5 line-clamp-4 text-white">
                    {{ $card['description'] }}
                </p>

                <div class="mt-6 flex items-end justify-between gap-4">
                    <span class="text-small text-[#CBDEEA]">
                        {{ $card['publishedAt'] }}
                    </span>
                    <x-buttons.btn-more url="{{$card['url']}}" text="подробнее" class="text-accent-add hover:text-[#9DDEE0]" />
                </div>
            </article>
        @endforeach
    </div>

    <x-buttons.btn url="{{$data['btnUrl']}}" text="{{$data['btnLabel']}}" type="btn-secondary" class="self-center md:self-end" />
</x-layout.section-full>
