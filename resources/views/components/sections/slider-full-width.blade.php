@props([
    'sliders' => [
        [
            'title' => 'Научно-исследовательский<br>
                и проектный институт градостроительства',
            'iconUrl' => '/images/icon-slide1.png',
            'iconAlt' => 'icon',
            'bgImageUrl' => '/resources/images/slider/slide1.jpg',
            'imageAlt' => 'image',
        ],
        [
            'title' => 'генеральный план ',
            'iconUrl' => '/images/icon-slide1.png',
            'iconAlt' => 'icon',
            'bgImageUrl' => '/resources/images/slider/slide1.jpg',
            'imageAlt' => 'image',
        ],
        [
            'title' => 'Научно-исследовательский',
            'iconUrl' => '/images/icon-slide1.png',
            'iconAlt' => 'icon',
            'bgImageUrl' => '/resources/images/slider/slide1.jpg',
            'imageAlt' => 'image',
        ],
    ],
])

<section class="relative mx-auto flex w-full max-w-1600 items-center justify-center">
    <div class="swiper js-main-slider relative">
        <div class="swiper-wrapper">
            @foreach ($sliders as $slide)
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
    <div class="absolute bottom-2 inline-flex flex-row items-center justify-between gap-4">
        <button type="button" class="js-slider-prev js-main-slider-prev z-20 h-10 w-10 cursor-pointer"></button>
        <div class="js-main-slider-pagination z-20 flex items-center justify-center"></div>
        <button type="button" class="js-slider-next js-main-slider-next z-20 h-10 w-10 cursor-pointer"></button>
    </div>
</section>
