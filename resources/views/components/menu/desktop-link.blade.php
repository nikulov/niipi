@props([
    'href' => '#',
    'blank' => false,
    ])

@if ($href === '#' || $href === '')
    
    <button
            type="button"
            {{ $attributes->class([
                'menu-link menu-link-btn z-20 cursor-default',
            ]) }}
    >
        
        {{ $slot }}
        
    </button>
    
@else
    
    <a href="{{$href}}"
       @if($blank) target="_blank" rel="noopener noreferrer" @endif
            {{ $attributes->class([
                'menu-link',
                'menu-link-active' => trim(url()->current(), '/') === trim(url($href), '/'),
            ]) }}>
        
        {{ $slot }}
        
    </a>
    
@endif