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
        <div
            class="bg-accent absolute top-0 flex h-4.5 w-full items-start justify-between"
            style="clip-path: polygon(0 0, 100% calc(100% - 18px), calc(100% - 18px) 100%, 20px 100%, 0 calc(100% - 18px))"
        >
            <div
                class="absolute top-0 left-1/2 flex h-4.5 w-18 -translate-x-1/2 items-center justify-center bg-[url('/resources/images/layout/item-top-content.svg')] bg-no-repeat"
            ></div>

            <div
                class="absolute top-1/2 right-2.5 flex h-1.5 w-20 -translate-y-1/2 items-center justify-center bg-[url('/resources/images/layout/item-top-right-content.svg')] bg-no-repeat"
            ></div>
        </div>

        @yield("main_section")

        <div
            class="bg-accent absolute right-0 -bottom-4.5 flex h-4.5 w-[60%] items-start justify-between md:w-[40%]"
            style="clip-path: polygon(0 0, 100% calc(100% - 18px), 100% 100%, 20px 100%, 0 calc(100% - 18px))"
        >
            <div
                class="absolute top-1/2 left-4 flex h-1.5 w-20 -translate-y-1/2 items-center justify-center bg-[url('/resources/images/layout/item-bottom-left-content.svg')] bg-no-repeat"
            ></div>

            <div
                class="absolute top-1/2 right-2 hidden h-2.5 w-40 -translate-y-1/2 bg-[url('/resources/images/layout/item-bottom-right-content.svg')] bg-no-repeat md:block"
            ></div>
        </div>
    </div>
</div>
