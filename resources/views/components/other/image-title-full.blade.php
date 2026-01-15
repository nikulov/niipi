<div class="relative px-inner-section-x max-w-1600 min-h-74 mx-auto flex flex-col justify-end bg-cover bg-right
"
     style="
        background-image:
            linear-gradient(
                90deg,
                rgba(46,72,95,1) 0%,
                rgba(46,72,95,1) 36%,
                rgba(46,72,95,0) 63%
            ),
            url('{{ public_asset($imageUrl) }}');
    ">
    
    <x-icon.icon-left-top-image class="hidden 2xl:block absolute z-10 w-[104px] h-[300px] fill-white -translate-x-4.5 md:-translate-x-8"/>
    
    <div class="absolute left-0 top-0  w-full h-full bg-cover bg-center md:bg-left
                bg-[url('/resources/images/layout/bg-mask-top-image.png')]
    "></div>
    
    <div class="max-w-1290 w-full mx-auto flex flex-row flex-wrap items-center justify-start gap-10 py-16">
        <img src={{public_asset($iconUrl)}} class="w-[120px] z-10" alt="{{$iconAlt}}"/>
        <h1 class="text-white z-10">{!!$title!!}</h1>
    </div>

</div>