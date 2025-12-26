@props(['url' => '#'])

<a href="{{$url}}" class="flex flex-col gap-2 justify-center items-center w-22 aspect-square bg-primary/60">
    {{$slot}}
</a>
