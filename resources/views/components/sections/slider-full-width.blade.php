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
                    <x-other.image-title-full
                            icon-url="{{$slide['iconUrl']}}"
                            icon-alt="{{$slide['iconAlt']}}"
                            image-url="{{$slide['bgImageUrl']}}"
                            title="{{$slide['title']}}"
                            url="{{$slide['url']}}"
                    />
                </div>
                
            @endforeach
           
        </div>
    </div>
    <div class="absolute inline-flex bottom-2 flex-row justify-between items-center gap-4">
        <button type="button" class="js-slider-prev js-main-slider-prev cursor-pointer w-10 h-10 z-20"></button>
        <div class="js-main-slider-pagination flex justify-center items-center z-20"></div>
        <button type="button" class="js-slider-next js-main-slider-next cursor-pointer w-10 h-10 z-20"></button>
    </div>
    
</div>

