@extends('layout.base')

@section('page.title', $page -> title ?? 'Новости')

@section('top_section')
    
    <x-sections.image-title-full-width title="НОВОСТИ, <br> МЕРОПРИЯТИЯ, СОБЫТИЯ" bg-image-url="images/layout/news-header.jpg"/>

@endsection


@section('main_section')

    <x-sections.text-full/>
    <x-sections.gallery/>
    
    
    

@endsection


@section('bottom_section')

@endsection