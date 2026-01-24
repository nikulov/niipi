@props([
    'type' => '',
    'isOpen' => 'false',
])

<div
    x-data="{ open: {{ $isOpen ? 'true' : 'false' }} }"
    class="border-primary dark:border-accent-dark relative min-w-70 overflow-hidden border [clip-path:polygon(8px_0,100%_0,100%_calc(100%-8px),calc(100%-8px)_100%,0_100%,0_8px)]"
>
    <div
        class="border-primary dark:border-accent-dark absolute top-0.75 -left-0.75 z-60 h-px min-h-px w-3 min-w-3 -rotate-45 border-b"
    ></div>

    {{ $slot }}

    @if ($type == 'white')
        <div class="bg-primary absolute bottom-0 h-4 w-full"></div>
    @endif

    <div
        class="border-primary dark:border-accent-dark absolute -right-0.75 bottom-0.75 z-60 h-px min-h-px w-3 min-w-3 -rotate-45 border-b"
    ></div>
</div>
