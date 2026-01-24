@props([
    'url' => '#',
    'iconUrl' => '#',
])

<a href="{{ $url }}" class="dark:[&>svg]:fill-white-dark! flex h-10 w-10 items-center justify-center [&>svg]:fill-white!">
    {!! file_get_contents(public_asset($iconUrl)) !!}
</a>
