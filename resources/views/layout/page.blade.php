@extends('layout.base')
@php use App\Services\PageRenderer; @endphp

@section('page.title', $page -> title ?? 'TEST')

@section('top_section')
    {!! app(PageRenderer::class)->renderSection($page, 'top_section') !!}
@endsection

@section('main_section')
    {!! app(PageRenderer::class)->renderSection($page, 'main_section') !!}
@endsection

@section('bottom_section')
    {!! app(PageRenderer::class)->renderSection($page, 'bottom_section') !!}
@endsection