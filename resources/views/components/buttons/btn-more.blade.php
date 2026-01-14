@props([
    'url' => '#',
    'text' => 'подробнее'
])

<a href="{{$url}}" {{ $attributes->class("text-normal font-normal flex items-center gap-1 mr-3") }}>
                        <span class=" relative
                            after:content-['+'] after:text-[22px]
                            after:absolute after:w-3 after:h-3 after:-top-3
                        ">[&nbsp;{{$text}}&nbsp;]</span>
</a>
