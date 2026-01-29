<x-layout.section-full bg-image-url="{{$data['bgImageUrl']}}">
    <h2 class="dark:text-accent-add-dark text-white">
        {!! nl2br(e($data['title'])) !!}
    </h2>

    <div class="grid grid-cols-1 gap-x-32 gap-y-15 md:grid-cols-2 md:gap-y-10">
        @foreach ($cards as $card)
            <div class="relative flex w-full flex-col">
                <a href="{{ $card['url'] }}">
                    <div class="relative">
                        <img src="{{ $card['thumbnail'] }}" alt="" class="bg-primary/20 h-75 w-full object-cover" />
                        <div class="bg-primary/20 absolute inset-0"></div>
                    </div>

                    <div class="flex flex-col">
                        <p class="text-normal dark:text-white-dark mt-5 line-clamp-3 text-white uppercase">
                            {{ $card['title'] }}
                        </p>
                    </div>
                </a>
            </div>
        @endforeach
    </div>

    <x-buttons.btn url="{{$data['btnUrl']}}" text="{{$data['btnLabel']}}" type="btn-secondary" class="self-center md:self-end" />
</x-layout.section-full>
