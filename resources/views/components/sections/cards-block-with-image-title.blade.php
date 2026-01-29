@props([
    'title' => '',
    'cards' => [
        [
            'cardUrl' => '#',
            'cardFileUrl' => '#',
            'imageAlt' => 'card',
            'cardTitle' => 'ФИО',
            'cardDescription' => 'должность',
        ],
    ],
])

<section class="my-inner-section-y px-inner-section-x container mx-auto flex w-full max-w-1242 flex-col">
    @if ($title)
        <h2 class="mb-after-title text-primary dark:text-accent-add-dark">
            {!! nl2br(e($title)) !!}
        </h2>
    @endif

    <div class="flex flex-row flex-wrap justify-center gap-9.25 xl:justify-start">
        @foreach ($cards as $card)
            <x-other.card-image-title
                card-url="{{$card['cardUrl']}}"
                card-file-url="{{$card['cardFileUrl']}}"
                image-alt="{{$card['imageAlt']}}"
                card-title="{{$card['cardTitle']}}"
                card-description="{{$card['cardDescription']}}"
            />
        @endforeach
    </div>
</section>
