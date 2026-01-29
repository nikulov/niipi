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
    class="dark:bg-background-dark items-top relative flex w-full max-w-91 flex-col justify-start bg-white bg-[url('/resources/images/layout/card-wave.png')] bg-cover bg-no-repeat pb-10.5 bg-blend-multiply md:max-w-92"
>
    <a @if(!$isPdf && $cardUrl) href="{{ $cardUrl }}" @endif class="relative flex flex-col items-center justify-center">
        @if ($isPdf)
            <a href="{{ $fileUrl }}" download class="mb-auto block h-full w-full">
                <div class="h-full overflow-hidden">
                    <iframe src="{{ $fileUrl }}#view=FitH" class="h-full w-full" loading="lazy"></iframe>
                </div>
            </a>
        @else
            <img src="{{ $fileUrl }}" alt="{{ $imageAlt ?? '' }}" class="h-auto max-h-65 w-full object-cover object-top" loading="lazy" />
        @endif

        @if ($cardTitle)
            <p class="text-primary text-big dark:text-white-dark relative z-10 mt-4.5 px-8 text-center font-bold whitespace-pre-line">
                {{ $cardTitle }}
            </p>
        @endif

        @if ($cardDescription)
            <p class="text-text text-medium relative z-10 px-8 text-center whitespace-pre-line dark:text-[#A7C1D2]">
                {{ $cardDescription }}
            </p>
        @endif
    </a>
</div>
