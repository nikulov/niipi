@props([
    'type' => '',
    'isOpen' => 'false'
])

<div x-data="{ open:  {{ $isOpen ? 'true' : 'false' }}  }" class=" relative overflow-hidden
        [clip-path:polygon(8px_0,100%_0,100%_calc(100%-8px),calc(100%-8px)_100%,0_100%,0_8px)]
        border border-primary">
    <div class="absolute top-[3px] left-[-3px] -rotate-45 min-w-3 w-3 min-h-px h-px border-b border-primary z-60"></div>
    
    {{$slot}}
    
    @if($type == 'white')
        <div class="absolute bottom-0 w-full h-4 bg-primary"></div>
    @endif
    <div class="absolute bottom-[3px] right-[-3px] -rotate-45 min-w-3 w-3 min-h-px h-px border-b border-primary z-60"></div>
</div>
