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
    
    <script>
        document.documentElement.classList.toggle(
            "dark",
            localStorage.theme === "dark"
            || (!("theme" in localStorage)
            && window.matchMedia("(prefers-color-scheme: dark)").matches),
        );
    </script>
    
    <style>
        [x-cloak] { display: none !important; }
    </style>
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @livewireStyles
    
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    @if(!empty($settings?->code_header))
        {!! $settings->code_header !!}
    @endif

</head>
<body class="font-inter text-text antialiased">
    @if(!empty($settings?->code_body_top))
        {!! $settings->code_body_top!!}
    @endif
    <div class="flex flex-col justify-between min-h-screen">
        
        @include('includes.header')
        
        <main class="grow max-w-1600 w-full mx-auto bg-background-light dark:bg-background-dark ">
            
            <div class="container max-w-1600 w-full h-full to-top-white border-y border-accent">
                @yield('top_section')
            </div>
            
            @include('layout.content-block')
            
            <div class="container max-w-1600 w-full mt-16 h-full to-top-white">
                @yield('bottom_section')
            </div>
        
        </main>
        
        @include('includes.footer')
    
    </div>
    
    <x-buttons.to-top/>
    
    @livewireScripts
    
    @if(!empty($settings?->code_body_bottom))
        {!! $settings->code_body_bottom!!}
    @endif
    
    <div
            x-data="{ show: false }"
            x-on:theme-fade.window="
            show = true;
            setTimeout(() => show = false, 260);"
            x-show="show"
            class="fixed max-w-1600 mx-auto inset-0 z-9999 pointer-events-none bg-primary/30 dark:bg-primary/30"
            
            x-transition:enter="transition-opacity ease-out duration-100"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            
            x-transition:leave="transition-opacity ease-out duration-160"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
    ></div>

</body>
</html>