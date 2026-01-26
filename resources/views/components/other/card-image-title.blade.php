@props([
    'cardUrl' => '#',
    'cardFileUrl' => '#',
    'imageAlt' => 'card',
    'cardTitle' => 'ФИО',
    'cardDescription' => 'должность',
])

@php
    $fileUrl = public_asset($cardFileUrl);
    $ext = strtolower(pathinfo($cardFileUrl, PATHINFO_EXTENSION));
    $isPdf = $ext === 'pdf';
@endphp

<div
    class="dark:bg-background-dark border-accent dark:border-accent-add-dark relative flex w-full max-w-full flex-col items-center justify-center border bg-white pb-4 md:max-w-92"
>
    <a href="{{ $isPdf ? '' : $cardUrl }}" class="relative flex flex-col items-center justify-center">
        @if ($isPdf)
            <a href="{{ $fileUrl }}" download class="dark:bg-background-dark mb-auto block bg-white">
                <div class="border-border dark:border-border-dark overflow-hidden border">
                    <iframe src="{{ $fileUrl }}#view=FitH" class="h-56 w-full" loading="lazy"></iframe>
                </div>

                <div class="mt-3 flex items-center justify-between">
                    <span class="text-accent text-sm underline">Скачать</span>
                </div>
            </a>
        @else
            <img src="{{ $fileUrl }}" alt="{{ $imageAlt ?? '' }}" class="h-auto w-full object-cover" loading="lazy" />
        @endif

        @if ($cardTitle)
            <p class="text-primary text-big dark:text-white-dark relative z-10 mt-3 mb-2">
                {{ $cardTitle }}
            </p>
        @endif

        @if ($cardDescription)
            <p class="text-normal dark:text-text-dark relative z-10">
                {{ $cardDescription }}
            </p>
        @endif
    </a>
</div>
