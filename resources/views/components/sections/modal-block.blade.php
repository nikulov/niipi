@props([
    'title' => null,
    'widthFull' => false,
    'modalHtml' => '',
    'modalId' => null,
])

<section
    class="relative h-0 w-0"
    x-data="{
        open: false,
        hash: '#{{ $modalId }}',
        sync() {
            this.open = window.location.hash === this.hash
        },
        close() {
            history.pushState(
                null,
                null,
                window.location.pathname + window.location.search,
            )
            this.open = false
        },
    }"
    x-init="
        sync()
        window.addEventListener('hashchange', () => sync())
    "
>
    <div
        class="fixed inset-0 z-60 w-full bg-black/50"
        x-cloak
        x-show="open"
        x-trap.noscroll.nofocus="open"
        @keydown.escape.window="close()"
    >
        <div class="absolute h-full max-h-full w-full overflow-y-scroll overscroll-contain scheme-light dark:scheme-dark">
            <div
                class="{{ $widthFull ? 'max-w-1290' : 'max-w-228' }} bg-background-light dark:bg-background-dark relative mx-auto mt-10 w-full"
            >
                <x-layout.main-section-border>
                    <div class="px-inner-section-x to-top-dark relative flow-root">
                        <div @click.prevent="close" class="cursor-pointer">
                            <x-icon.icon-close-cross
                                class="fill-accent-add dark:fill-accent-add-dark top-inner-section-y absolute right-10 h-6 w-6"
                            />
                        </div>

                        @if ($title)
                            <h2 class="text-primary dark:text-accent-add-dark mb-6">
                                {{ $title }}
                            </h2>
                        @endif

                        {!! $modalHtml !!}
                    </div>
                </x-layout.main-section-border>
            </div>
        </div>
    </div>
</section>
