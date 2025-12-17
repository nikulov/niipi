@extends('layout.base')

@section('page.title', $page -> title ?? 'Новости')

@section('top_section')
    
    <x-pages.image-title-full-width title="НОВОСТИ, <br> МЕРОПРИЯТИЯ, СОБЫТИЯ" bg-image-url="images/layout/news-header.jpg"/>

@endsection


@section('main_section')

    <x-news.title/>
    <x-news.image-full/>
    <x-news.text-full/>
    <x-news.image-text/>
    <x-news.title title="ФОТОМАТЕРИАЛЫ" type="h3"/>
    <x-news.gallery/>

@endsection


@section('bottom_section')

@endsection