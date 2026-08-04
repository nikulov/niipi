@props([
    'title' => '',
    'cards' => [],
])

@php
    $fallbackAlt = config('app.name');
@endphp

<section class="my-inner-section-y px-inner-section-x mx-auto flex w-full max-w-1242 flex-col gap-8">
    @if (! empty($title))
        <h3 class="text-primary dark:text-accent-add-dark text-center">
            {!! nl2br(e($title)) !!}
        </h3>
    @endif

    <div class="grid grid-cols-2 gap-4 sm:grid-cols-3 lg:grid-cols-5">
        @foreach ($cards as $card)
            <a href="{{ $card['url'] }}" class="group bg-primary/10 relative block aspect-square w-full overflow-hidden">
                @if (! empty($card['thumbnail']))
                    <img
                        src="{{ $card['thumbnail'] }}"
                        alt="{{ $card['title'] ?: $fallbackAlt }}"
                        loading="lazy"
                        class="h-full w-full object-cover transition duration-300 group-hover:scale-105"
                    />
                @endif

                <div class="bg-primary/40 group-hover:bg-primary/60 absolute inset-0 transition"></div>
                <div class="absolute inset-x-0 bottom-0 p-3">
                    <p class="text-small line-clamp-3 font-bold text-white">
                        {{ $card['title'] }}
                    </p>
                </div>
            </a>
        @endforeach
    </div>
</section>
