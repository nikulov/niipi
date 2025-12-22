@extends('layout.base')

@section('page.title', $page -> title ?? 'TEST')

@section('top_section')
    {!! $renderer->renderSection($page, 'top') !!}
@endsection

@section('main_section')
    {!! $renderer->renderSection($page, 'main') !!}
@endsection

@section('bottom_section')
    {!! $renderer->renderSection($page, 'bottom') !!}
@endsection