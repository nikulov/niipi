<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    
    @isset($meta['description'])
        <meta name="description" content="{{ $meta['description'] }}">
    @endisset
    
    @isset($meta['keywords'])
        <meta name="keywords" content="{{ $meta['keywords'] }}">
    @endisset
    
    <title>@yield('page.title', $settings->title ?? config('app.name'))</title>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    @if(!empty($settings?->code_header))
        {!! $settings->code_header !!}
    @endif

</head>
<body class="font-inter text-text antialiased"
      :class="menuOpen ? 'overflow-hidden' : ''"
>
    @if(!empty($settings?->code_body_top))
        {!! $settings->code_body_top!!}
    @endif
    <div class="flex flex-col justify-between min-h-screen">
        
        @include('includes.header')
        
        <main class="flex-grow max-w-1600 w-full mx-auto bg-background-light">
            
            <div class="container max-w-1600 w-full h-full">
                @yield('top_section')
            </div>
            
            @include('layout.content-block')
            
            <div class="container max-w-1600 w-full mt-16 h-full">
                @yield('bottom_section')
            </div>
        
        </main>
        
        @include('includes.footer')
    
    </div>
    @if(!empty($settings?->code_body_bottom))
        {!! $settings->code_body_bottom!!}
    @endif
</body>
</html>