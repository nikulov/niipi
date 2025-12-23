@props([
    'url' => '#',
    'text' => 'кнопка',
    'type' => 'btn-primary',
])

<a href="{{ $url }}" {{ $attributes->class("group btn no-underline {$type}-bg") }}
>
    <div class="absolute top-[3px] left-[-3px] -rotate-45 min-w-[12px] w-[12px] min-h-[1px] h-[1px] border-b {{$type}}-bg transition-all duration-300"></div>
    
    <span class="{{$type}}-text btn-text">{{$text}}</span>
    
    <div class="absolute bottom-[3px] right-[-3px] -rotate-45 min-w-[12px] w-[12px] min-h-[1px] h-[1px] border-b {{$type}}-bg transition-all duration-300"></div>
</a>