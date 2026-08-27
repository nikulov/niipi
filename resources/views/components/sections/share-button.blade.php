@props([
    'btnLabel' => 'Кнопка',
    'btnUrl' => '#',
    'btnType' => 'btn-primary',
    'btnPosition' => 'end',
    'blank' => false,
    'socials' => [],
    'showCopy' => true,
])

<section
    class="px-inner-section-x my-inner-section-y justify-{{ $btnPosition }} container mx-auto flex max-w-1242 flex-row max-md:justify-center"
>
    <div class="flex flex-row items-center gap-2">
        @if (filled($socials))
            <div
                x-data="{
                    open: false,
                    copied: false,

                    // the admin stores share targets as templates; the page they point at is this one
                    share(template) {
                        return template
                            .replaceAll('{url}', encodeURIComponent(location.href))
                            .replaceAll('{title}', encodeURIComponent(document.title))
                    },

                    copy() {
                        navigator.clipboard?.writeText(location.href).then(() => {
                            this.copied = true
                            setTimeout(() => (this.copied = false), 2000)
                        })
                    },
                }"
                @click.outside="open = false"
                @keydown.escape.window="open = false"
                class="relative"
            >
                <button
                    type="button"
                    @click="open = ! open"
                    :aria-expanded="open"
                    aria-label="{{ __('page.share') }}"
                    class="{{ $btnType }}-bg relative inline-flex h-12.25 min-h-12.25 w-12.25 min-w-12.25 cursor-pointer items-center justify-center transition-all duration-300 [clip-path:polygon(8px_0,100%_0,100%_calc(100%-8px),calc(100%-8px)_100%,0_100%,0_8px)]"
                >
                    <x-icon.icon-share class="{{ $btnType }}-text h-10 w-10 fill-white" />
                </button>

                <div
                    x-cloak
                    x-show="open"
                    x-transition:enter="transition duration-300 ease-out"
                    x-transition:enter-start="-translate-x-4 opacity-0"
                    x-transition:enter-end="translate-x-0 opacity-100"
                    x-transition:leave="transition duration-200 ease-in"
                    x-transition:leave-start="translate-x-0 opacity-100"
                    x-transition:leave-end="-translate-x-4 opacity-0"
                    class="absolute top-0 left-10 z-20"
                >
                    @if ($showCopy)
                        <span
                            x-cloak
                            x-show="copied"
                            x-transition.opacity
                            class="bg-primary dark:bg-accent-dark text-small absolute right-0 bottom-full mb-2 rounded px-2 py-1 whitespace-nowrap text-white"
                        >
                            {{ __('page.link_copied') }}
                        </span>
                    @endif

                    <div class="flex h-12.25 min-h-12.25 min-w-54.5 flex-row items-center justify-center gap-2 bg-[#7ba2ba] px-3">
                        @foreach ($socials as $social)
                            <a
                                :href="share(@js($social['shareUrl'] ?? ''))"
                                target="_blank"
                                rel="noopener noreferrer"
                                aria-label="{{ $social['title'] ?? '' }}"
                                class="dark:text-text-dark flex h-9 w-9 items-center justify-center text-white transition-opacity duration-300 hover:opacity-70 [&>svg]:h-9 [&>svg]:w-9 [&>svg]:fill-white!"
                            >
                                {!! inline_svg($social['iconUrl'] ?? null) !!}
                            </a>
                        @endforeach

                        @if ($showCopy)
                            <button
                                type="button"
                                @click="copy()"
                                :aria-label="copied ? @js(__('page.link_copied')) : @js(__('page.copy_link'))"
                                class="group dark:text-text-dark relative flex h-9 w-9 cursor-pointer items-center justify-center text-white"
                            >
                                <x-icon.icon-link class="h-6 w-6 transition-opacity duration-300 group-hover:opacity-70" />

                                {{-- hidden while the «link copied» tooltip is up: both sit above the bar and would overlap --}}
                                <span
                                    x-show="! copied"
                                    class="text-white-dark dark:text-primary absolute bottom-full left-1/2 mb-2.5 w-fit -translate-x-1/2 rounded bg-[#2e3445] px-3 py-1 text-xs whitespace-nowrap opacity-0 transition-opacity duration-200 group-hover:opacity-100 dark:bg-[#EFF0F2]"
                                >
                                    {{ __('page.copy_link') }}
                                </span>
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        @endif

        <x-buttons.btn url="{{ $btnUrl }}" text="{{ $btnLabel }}" type="{{ $btnType }}" blank="{{ $blank }}" />
    </div>
</section>
