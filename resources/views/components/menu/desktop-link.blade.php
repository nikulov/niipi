@props([
    'href' => '#',
    'blank' => false,
    ])

@if ($href === '#' || $href === '')
    
    <span class="menu-link cursor-default group-hover:text-accent-add">
        
        {{ $slot }}
        
    </span>
    
@else
    
    <a href="{{$href}}"
       @if($blank) target="_blank" rel="noopener noreferrer" @endif
            {{ $attributes->class([
                'menu-link',
                'menu-link-active' => trim(url()->current(), '/') === trim(url($href), '/'),
            ]) }}
    >
        
        {{ $slot }}
        
    </a>
    
@endif