@props([
    'url' => '#',
    'text' => 'кнопка',
    'type' => 'btn-primary',
    'blank' => false,
])

<a
    href="{{ $url }}"
    {{ $attributes->class("group btn no-underline {$type}-bg") }}
    @if($blank) target="_blank" rel="noopener noreferrer"@endif
>
    <div class="absolute top-[3px] left-[-3px] -rotate-45 min-w-3 w-3 min-h-px h-px border-b {{$type}}-bg transition-all duration-300"></div>
    
    <span class="{{$type}}-text btn-text">{{$text}}</span>
    
    <div class="absolute bottom-[3px] right-[-3px] -rotate-45 min-w-3 w-3 min-h-px h-px border-b {{$type}}-bg transition-all duration-300"></div>
</a>