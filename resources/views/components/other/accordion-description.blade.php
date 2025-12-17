<div class="grid overflow-hidden transition-all duration-400 ease-in"
     :class="open ? 'grid-rows-[1fr] opacity-100' : 'grid-rows-[0fr] opacity-0'">
    <div class="min-h-0">
        
        {{$slot}}

    </div>
</div>