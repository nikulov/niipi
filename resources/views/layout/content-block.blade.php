@props([
    "bgForMainSection" => "",
])

<div class="px-inner-section-x to-top-dark">
    <div
        class="dark:bg-background-dark-add border-border relative container mx-auto mt-16 min-h-20 w-full max-w-1290 border-b bg-white bg-contain bg-top bg-no-repeat pt-px bg-blend-multiply"
        @if (! empty($bgForMainSection))
            style="background-image: url({{ public_asset($bgForMainSection) }});"
        @endif
    >
        <x-layout.main-section-border>
            @yield("main_section")
        </x-layout.main-section-border>
    </div>
</div>
