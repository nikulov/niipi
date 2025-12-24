@props([
    'href' => '#',
    'blank' => false,
    ])

<a href="{{$href}}"
   @if($blank) target="_blank" rel="noopener noreferrer" @endif
        {{ $attributes->class([
            'menu-link',
            'menu-link-active' => trim(url()->current(), '/') === trim(url($href), '/'),
        ]) }}>
    
    {{ $slot }}
</a>