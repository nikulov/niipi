@props([
    'url' => '#',
    'text' => 'подробнее'
])

<a href="{{$url}}" {{$attributes->class("relative text-big font-bold flex items-center gap-1 mr-3 transition-colors duration-300") }}>
                        <span class=" relative after:font-bold">[&nbsp;{{$text}}&nbsp;]</span>
                        <x-icon.icon-arrow-down-add class="absolute -top-1 -right-2 w-2.5 h-2.5"/>
</a>
