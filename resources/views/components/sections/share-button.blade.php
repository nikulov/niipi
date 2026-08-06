@props([
    'btnLabel' => 'Кнопка',
    'btnUrl' => '#',
    'btnType' => 'btn-primary',
    'btnPosition' => 'end',
    'blank' => false,
])

<section
    class="px-inner-section-x my-inner-section-y md:justify-{{ $btnPosition }} container mx-auto flex max-w-1242 flex-row justify-center"
>
    <div
        x-data="{
            open: false,
            copied: false,

            // share targets point at the page the block sits on, nothing is configured in the admin
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
        class="flex flex-row items-center gap-2"
    >
        <div class="relative">
            <button
                type="button"
                @click="open = ! open"
                :aria-expanded="open"
                aria-label="{{ __('page.share') }}"
                class="{{ $btnType }}-bg relative inline-flex h-12.25 min-h-12.25 w-12.25 min-w-12.25 cursor-pointer items-center justify-center border transition-all duration-300 [clip-path:polygon(8px_0,100%_0,100%_calc(100%-8px),calc(100%-8px)_100%,0_100%,0_8px)]"
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
                <span
                    x-cloak
                    x-show="copied"
                    x-transition.opacity
                    class="bg-primary dark:bg-accent-dark text-small absolute right-0 bottom-full mb-2 rounded px-2 py-1 whitespace-nowrap text-white"
                >
                    {{ __('page.link_copied') }}
                </span>

                <div
                    class="flex h-12.25 min-h-12.25 min-w-60 flex-row items-center justify-center gap-2 border border-[#7ba2ba] bg-[#7ba2ba] px-3"
                >
                    <a
                        :href="share('https://vk.com/share.php?url={url}&title={title}')"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="ВКонтакте"
                        class="dark:text-text-dark flex h-9 w-9 items-center justify-center text-white transition-opacity duration-300 hover:opacity-70"
                    >
                        <x-icon.icon-vk class="h-6 w-6" />
                    </a>

                    <a
                        :href="share('https://t.me/share/url?url={url}&text={title}')"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="Telegram"
                        class="dark:text-text-dark flex h-9 w-9 items-center justify-center text-white transition-opacity duration-300 hover:opacity-70"
                    >
                        <x-icon.icon-telegram class="h-6 w-6" />
                    </a>

                    <a
                        :href="share('https://max.ru/:share?text={url}')"
                        target="_blank"
                        rel="noopener noreferrer"
                        aria-label="MAX"
                        class="dark:text-text-dark flex h-9 w-9 items-center justify-center text-white transition-opacity duration-300 hover:opacity-70"
                    >
                        <x-icon.icon-max class="h-6 w-6" />
                    </a>

                    <button
                        type="button"
                        @click="copy()"
                        :aria-label="copied ? @js(__('page.link_copied')) : @js(__('page.copy_link'))"
                        class="dark:text-text-dark flex h-9 w-9 cursor-pointer items-center justify-center text-white transition-opacity duration-300 hover:opacity-70"
                    >
                        <x-icon.icon-link class="h-6 w-6" />
                    </button>
                </div>
            </div>
        </div>

        <x-buttons.btn url="{{ $btnUrl }}" text="{{ $btnLabel }}" type="{{ $btnType }}" blank="{{ $blank }}" />
    </div>
</section>
