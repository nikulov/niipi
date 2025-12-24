@props([
    'href' => '#',
    'blank' => false,
    ])

<a href="{{$href}}"
   @if($blank) target="_blank" rel="noopener noreferrer" @endif
       class="text-white hover:underline">
    {{ $slot }}
</a>