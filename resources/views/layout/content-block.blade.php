@props(
    [
        'bgForMainSection' => '',
    ]
)

<div class="container pt-[1px] relative mx-auto max-w-1290 w-full min-h-20 bg-white mt-16 border-b border-border bg-cover bg-no-repeat "
     @if( ! empty($bgForMainSection))
         style="background-image: url({{public_asset($bgForMainSection)}});"
     @endif
>
    
    <div class="absolute top-0 w-full h-4.5 flex justify-between items-start bg-accent"
         style="clip-path: polygon(0 0, 100% calc(100% - 18px), calc(100% - 18px) 100%, 20px 100%, 0 calc( 100% - 18px));
    ">
        
        <div class="absolute top-0 left-1/2 -translate-x-1/2 w-18 h-4.5 bg-no-repeat flex justify-center items-center
             bg-[url('/resources/images/layout/item-top-content.svg')]
        "></div>
        
        <div class="absolute top-1/2 -translate-y-1/2 right-2 w-20 h-1.5 bg-no-repeat flex justify-center items-center
             bg-[url('/resources/images/layout/item-top-right-content.svg')]
        "></div>
    
    </div>
    
    
    @yield('main_section')
    
    
    <div class="absolute bottom-[-18px] right-0 w-1/2 h-4.5 flex justify-between items-start bg-accent"
         style="clip-path: polygon(0 0, 100% calc(100% - 18px), 100% 100%, 20px 100%, 0 calc( 100% - 18px));
    ">
        
        <div class=" absolute top-1/2 -translate-y-1/2 left-4 w-20 h-1.5 bg-no-repeat flex justify-center items-center
             bg-[url('/resources/images/layout/item-bottom-left-content.svg')]
        "></div>
        
        <div class="absolute top-1/2 -translate-y-1/2 right-2 w-40 h-2.5 bg-no-repeat
             bg-[url('/resources/images/layout/item-bottom-right-content.svg')]
        "></div>
    
    </div>

</div>