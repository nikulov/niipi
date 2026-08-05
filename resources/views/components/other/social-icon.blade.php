@props([
    'url' => '#',
    'iconUrl' => '#',
])

@php
    $svg = inline_svg($iconUrl);
@endphp

<a href="{{ $url }}" class="dark:[&>svg]:fill-white-dark! flex h-10 w-10 items-center justify-center [&>svg]:fill-white!">
    {!! $svg !!}
</a>
