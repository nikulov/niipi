<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="description" content="{{ $meta['description'] ?? ($settings->description ?? '') }}" />
        <link rel="icon" href="{{ public_asset('/images/favicon.ico') }}" sizes="any" />

        <title>@yield('page.title', $settings->title ?? config('app.name'))</title>

        <script>
            document.documentElement.classList.toggle(
                'dark',
                localStorage.themeSite === 'dark' ||
                    (!('themeSite' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
            );
        </script>

        @livewireStyles
        @vite(['resources/css/app.css', 'resources/js/app.js'])

        {!! $settings->code_header ?? '' !!}
    </head>
    <body class="font-inter text-text mx-auto max-w-1600 antialiased">
        {!! $settings->code_body_top ?? '' !!}

        <div class="flex min-h-screen flex-col">
            @include('includes.header')

            <main class="bg-background-light dark:bg-background-dark mx-auto w-full flex-1">
                <div class="to-top-white border-accent container h-full w-full border-y">
                    @yield('top_section')
                </div>

                @include('layout.content-block')

                <div class="to-top-white container mt-16 h-full w-full">
                    @yield('bottom_section')
                </div>
            </main>

            @include('includes.footer')
        </div>

        <x-buttons.to-top />

        @livewireScripts

        {!! $settings->code_body_bottom ?? '' !!}

        <div
            x-data="{ show: false }"
            x-on:theme-fade.window="
                show = true
                setTimeout(() => (show = false), 260)
            "
            x-show="show"
            x-cloak
            class="bg-primary/30 dark:bg-primary/30 pointer-events-none fixed inset-0 z-9999 mx-auto max-w-1600"
            x-transition:enter="transition-opacity duration-100 ease-out"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-100"
            x-transition:leave="transition-opacity duration-160 ease-out"
            x-transition:leave-start="opacity-100"
            x-transition:leave-end="opacity-0"
        ></div>
    </body>
</html>
