<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />
        <meta name="csrf-token" content="{{ csrf_token() }}" />
        <meta name="description" content="{{ $meta['description'] ?? ($settings->description ?? '') }}" />

        <link rel="icon" type="image/svg+xml" href="{{ public_asset('/images/favicon/favicon.svg') }}" />
        <link rel="icon" type="image/png" sizes="32x32" href="{{ public_asset('/images/favicon/favicon-32x32.png') }}" />
        <link rel="icon" type="image/png" sizes="16x16" href="{{ public_asset('/images/favicon/favicon-16x16.png') }}" />
        <link rel="apple-touch-icon" sizes="180x180" href="{{ public_asset('/images/favicon/apple-touch-icon.png') }}" />
        <link rel="manifest" href="{{ public_asset('/images/favicon/site.webmanifest') }}" />
        <link rel="icon" href="{{ public_asset('/images/favicon/favicon.ico') }}" />

        <meta name="theme-color" content="#f0eff1" media="(prefers-color-scheme: light)" />
        <meta name="theme-color" content="#2c3140" media="(prefers-color-scheme: dark)" />

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
    <body class="font-inter text-text mx-auto antialiased">
        {!! $settings->code_body_top ?? '' !!}

        <div class="flex min-h-screen flex-col">
            @include('includes.header')

            <main class="bg-background-light dark:bg-background-dark mx-auto w-full max-w-1600 flex-1">
                <div class="to-top-white border-accent container h-full w-full max-w-1600 border-y">
                    @yield('top_section')
                </div>

                @include('layout.content-block')

                <div class="to-top-white container mt-16 h-full w-full max-w-1600">
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
