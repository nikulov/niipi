@props([
    'title' => 'Получения <br> генплана',
    'iconUrl' => '/images/icon-slide1.png',
    'iconAlt' => 'icon',
    'imageUrl' => 'images/layout/slide1.jpg',
    'imageAlt' => 'image',
])

<div class="relative px-inner-section-x max-w-1600 min-h-74 mx-auto flex flex-col justify-end bg-cover bg-center"
    style="background-image: url('{{public_asset($imageUrl)}}');">
    
    <div class="max-w-1290 w-full mx-auto flex flex-row flex-wrap items-center justify-start gap-10 py-16">
        
        <img src={{public_asset($iconUrl)}} class="w-[120px]" alt="{{$iconAlt}}"/>
       
        <h1 class="text-white">{!!$title!!}</h1>
        
    </div>
    
</div>