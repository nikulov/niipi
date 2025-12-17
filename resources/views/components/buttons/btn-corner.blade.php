@props([
    'url' => '#',
    'label' => 'подробнее',
])

<a href="{{$url}}" class="btn-corner btn-corner-color group">

    <div class="absolute top-[3px] left-[-3px] -rotate-45 min-w-[64px] w-[64px] min-h-[1px] h-[1px] border-b transition-all duration-300 btn-corner-color"></div>
    
    <span class="text-btn-corner text-white relative z-10 pl-2.5">{{$label}}</span>
    
    <div class="absolute bottom-[3px] right-[-3px] -rotate-45 min-w-[12px] w-[12px] min-h-[1px] h-[1px] border-b transition-all duration-300 btn-corner-color"></div>
</a>
