@props(['type'])

@php
    $map = [
        'question' => 'icon.icon-question',
        'plus'     => 'icon.icon-plus',
        'info'     => 'icon.icon-info',
    ];
@endphp

@if(isset($map[$type]))
    <x-dynamic-component :component="$map[$type]" {{ $attributes }}/>
@endif
