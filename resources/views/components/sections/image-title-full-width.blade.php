@props([
    'title' => 'Получения <br> генплана',
    'iconUrl' => '/images/icon-slide1.png',
    'iconAlt' => 'icon',
    'imageUrl' => 'images/layout/slide1.jpg',
    'imageAlt' => 'image',
])

<x-other.image-title-full icon-url="{{$iconUrl}}" icon-alt="{{$iconAlt}}" image-url="{{$imageUrl}}" title="{{$title}}"/>