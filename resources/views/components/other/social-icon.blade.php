@props(
    [
        'url' => '#',
        'iconUrl' => '#'
        
]
)

<a href="{{$url}}" class="flex h-10 w-10 items-center justify-center">
    {!! file_get_contents(public_asset($iconUrl)) !!}
</a>