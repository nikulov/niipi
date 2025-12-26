@props([
    'href' => '#',
    'blank' => false,
    ])

@if ($href === '#' || $href === '')
    
    <button
            type="button"
            {{ $attributes->class([
                'menu-link-mobile menu-link-btn z-20 cursor-default flex flex-row justify-center items-center',
            ]) }}
    >
        
        {{ $slot }}
    
    </button>

@else
    
    <a href="{{$href}}"
       @if($blank) target="_blank" rel="noopener noreferrer" @endif
            {{ $attributes->class([
                'menu-link-mobile',
                'menu-link-mobile-active' => trim(url()->current(), '/') === trim(url($href), '/'),
            ]) }}>
        
        {{ $slot }}
    
    </a>

@endif