<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1" />

        @isset($meta['description'])
            <meta name="description" content="{{ $meta['description'] }}" />
        @endisset

        @isset($meta['keywords'])
            <meta name="keywords" content="{{ $meta['keywords'] }}" />
        @endisset

        <title>@yield('page.title', $settings->title ?? config('app.name'))</title>

        <script>
            document.documentElement.classList.toggle(
                'dark',
                localStorage.themeSite === 'dark' ||
                    (!('themeSite' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches),
            );
        </script>

        <style>
            [x-cloak] {
                display: none !important;
            }
        </style>

        @vite(['resources/css/app.css', 'resources/js/app.js'])
        @livewireStyles

        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet" />

        @if (! empty($settings?->code_header))
            {!! $settings->code_header !!}
        @endif
    </head>
    <body class="font-inter text-text antialiased">
        @if (! empty($settings?->code_body_top))
            {!! $settings->code_body_top !!}
        @endif

        <div class="flex min-h-screen flex-col justify-between">
            @include('includes.header')

            <main class="bg-background-light dark:bg-background-dark mx-auto w-full max-w-1600 grow">
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

        @if (! empty($settings?->code_body_bottom))
            {!! $settings->code_body_bottom !!}
        @endif

        <div
            x-data="{ show: false }"
            x-on:theme-fade.window="
                show = true
                setTimeout(() => (show = false), 260)
            "
            x-show="show"
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
