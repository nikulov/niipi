@props([
    'urls' => ['gallery/posts/default/image-2-1766065371.jpg', 'gallery/posts/default/image-3-1766065371.jpg', 'gallery/posts/default/umg-1766065371.jpg', 'gallery/posts/default/main-slider-01-v2-3-1766065371.jpg', 'gallery/posts/default/standart-1766065371.jpg', 'gallery/posts/default/gpzu-1766065371.jpg', 'gallery/posts/default/kuzmina-aa-kazanysh-1-1766065371.jpeg'],
])

<section
    x-data="{
        open: false,
        startIndex: 0,
        openAt(i) {
            this.startIndex = i
            this.open = true

            this.$nextTick(() => {
                window.initGallerySliderForRoot(
                    this.$refs.modalRoot,
                    this.startIndex,
                )
            })
        },
        close() {
            this.open = false
        },
    }"
    class="my-inner-section-y px-inner-section-x mx-auto w-full max-w-1242"
>
    <div class="grid grid-cols-2 gap-4 md:grid-cols-4">
        @foreach ($urls as $i => $url)
            <button
                type="button"
                class="relative block aspect-4/3 w-full cursor-pointer overflow-hidden transition hover:opacity-80"
                @click="openAt({{ $i }})"
            >
                <img src="{{ public_asset($url) }}" class="h-full w-full object-cover" loading="lazy" alt="" />
            </button>
        @endforeach
    </div>

    <div
        x-show="open"
        x-transition.opacity
        x-cloak
        x-trap.noscroll="open"
        class="fixed inset-0 z-60 flex items-center justify-center bg-black/80"
        @click.self="close()"
        @keydown.escape.window="close()"
    >
        <div x-ref="modalRoot" class="relative mx-auto flex w-full max-w-1290 flex-col items-center px-4 pt-10">
            <button
                type="button"
                class="absolute top-0 right-2 z-30 cursor-pointer p-2 text-2xl leading-none text-white md:right-10"
                aria-label="Закрыть галерею"
                @click="close()"
            >
                <x-icon.icon-close-cross class="fill-accent hover:fill-accent-add h-6 w-6" />
            </button>

            <!-- Main -->
            <div
                class="swiper js-gallery-slider h-[min(80vh,700px)] w-full max-w-5xl max-lg:h-[min(70vh,560px)] max-sm:h-[min(60vh,420px)]"
                style="--swiper-navigation-color: #fff; --swiper-pagination-color: #fff"
            >
                <div class="swiper-wrapper h-full">
                    @foreach ($urls as $url)
                        <div class="swiper-slide flex h-full items-center justify-center">
                            <img src="{{ public_asset($url) }}" class="h-full max-h-full w-full object-contain" alt="" />
                        </div>
                    @endforeach
                </div>

                <button
                    type="button"
                    class="js-slider-prev js-gallery-slider-prev absolute top-1/2 left-0 z-20 h-10 w-10 -translate-y-1/2 cursor-pointer"
                ></button>
                <button
                    type="button"
                    class="js-slider-next js-gallery-slider-next absolute top-1/2 right-0 z-20 h-10 w-10 -translate-y-1/2 cursor-pointer"
                ></button>

                <div class="absolute right-0 bottom-2 left-0 z-20 flex justify-center">
                    <div class="js-gallery-slider-pagination"></div>
                </div>
            </div>

            <!-- Thumbs -->
            <div class="swiper js-gallery-slider-thumbs mt-6 h-35.5 w-full max-w-5xl max-sm:h-24">
                <div class="swiper-wrapper mx-auto h-full w-max!">
                    @foreach ($urls as $url)
                        <div class="swiper-slide h-35.5! w-50! cursor-pointer opacity-70 max-sm:h-24! max-sm:w-30!">
                            <img src="{{ public_asset($url) }}" class="h-full w-full object-cover" alt="" />
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</section>
