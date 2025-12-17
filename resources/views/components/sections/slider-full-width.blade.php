@props([
    'sliders' =>
    [
        [
            'title' => 'Научно-исследовательский<br>
                и проектный институт градостроительства',
            'iconUrl' => '/images/icon-slide1.png',
            'iconAlt' => 'icon',
            'bgImageUrl' => '/resources/images/slider/slide1.jpg',
            'imageAlt' => 'image'
        ],
        [
            'title' => 'генеральный план ',
            'iconUrl' => '/images/icon-slide1.png',
            'iconAlt' => 'icon',
            'bgImageUrl' => '/resources/images/slider/slide1.jpg',
            'imageAlt' => 'image'
        ],
        [
            'title' => 'Научно-исследовательский',
            'iconUrl' => '/images/icon-slide1.png',
            'iconAlt' => 'icon',
            'bgImageUrl' => '/resources/images/slider/slide1.jpg',
            'imageAlt' => 'image'
        ],
    ]
])


<div class="relative flex justify-center items-center max-w-1600 w-full mx-auto">
    <div class="swiper js-main-slider relative ">
        <div class="swiper-wrapper">
            
            @foreach($sliders as $slide)
                
                <div class="swiper-slide">
                    <div class="relative px-inner-section-x max-w-1600 min-h-74 mx-auto flex flex-col justify-end
                                bg-cover bg-center"
                        style="background-image: url('{{public_asset($slide["bgImageUrl"])}}');"
                    >
                        <div class="max-w-1290 w-full mx-auto flex flex-row flex-wrap items-center justify-start gap-10 py-16">
                            <img src="{{public_asset($slide['iconUrl'])}}" class="hidden md:block w-[120px]" alt="{{$slide['iconAlt']}}"/>
                            <h1 class="text-white">{{$slide['title']}}</h1>
                        </div>
                    </div>
                </div>
            
            @endforeach
           
        </div>
    </div>
    <div class="absolute inline-flex bottom-2 flex-row justify-between items-center gap-4">
        <button type="button" class="js-main-slider-prev cursor-pointer w-10 h-10 z-20"></button>
        <div class="js-main-slider-pagination flex justify-center items-center z-50"></div>
        <button type="button" class="js-main-slider-next cursor-pointer w-10 h-10 z-20"></button>
    </div>
    
</div>

