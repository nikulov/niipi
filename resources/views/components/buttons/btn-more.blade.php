@props([
    'url' => '#',
    'text' => 'подробнее'
])

<a href="{{$url}}" class="text-normal font-bold  flex items-center gap-1 mr-3
                                                    text-white
                                                    hover:text-accent-add">
                        <span class=" relative
                            after:content-['+'] after:text-[22px]
                            after:absolute after:w-3 after:h-3 after:top-[-12px]
                        ">[&nbsp;{{$text}}&nbsp;]</span>
</a>
